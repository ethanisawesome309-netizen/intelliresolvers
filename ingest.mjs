import { createClient } from 'redis';
import { GoogleGenerativeAI } from "@google/generative-ai";

const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const redis = createClient();
await redis.connect();

async function getEmbedding(text) {
    // UPDATED MODEL NAME FOR 2026
    const model = genAI.getGenerativeModel({ model: "gemini-embedding-001" }); 
    const result = await model.embedContent({
        content: { parts: [{ text }] },
        taskType: "RETRIEVAL_DOCUMENT",
    });
    return result.embedding.values;
}

async function runIngestion() {
    console.log("🚀 Starting HN Ingestion with gemini-embedding-001...");
    
    try {
        const topIdsRes = await fetch('https://hacker-news.firebaseio.com/v0/topstories.json');
        const topIds = (await topIdsRes.json()).slice(0, 20);

        for (const id of topIds) {
            const storyRes = await fetch(`https://hacker-news.firebaseio.com/v0/item/${id}.json`);
            const story = await storyRes.json();
            
            if (story && story.title) {
                console.log(`📝 Processing: ${story.title}`);
                const vectorArray = await getEmbedding(story.title);
                
                await redis.hSet(`hn:${id}`, {
                    title: story.title,
                    content: story.url || "No URL",
                    vector: Buffer.from(new Float32Array(vectorArray).buffer)
                });
            }
        }
        console.log("✅ Ingestion successfully completed!");
    } catch (error) {
        console.error("❌ Fatal Error during ingestion:", error);
    } finally {
        await redis.disconnect();
        process.exit();
    }
}

runIngestion();