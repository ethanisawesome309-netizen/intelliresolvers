import { createClient } from 'redis';
import { GoogleGenerativeAI } from "@google/generative-ai";
import fetch from 'node-fetch';

// Initialize APIs
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const redis = createClient();
await redis.connect();

async function getEmbedding(text) {
    const model = genAI.getGenerativeModel({ model: "text-embedding-004" });
    const result = await model.embedContent({
        content: { parts: [{ text }] },
        taskType: "RETRIEVAL_DOCUMENT",
    });
    return result.embedding.values;
}

async function runIngestion() {
    console.log("🚀 Starting HN Ingestion...");
    
    // 1. Get IDs of top stories
    const topIdsRes = await fetch('https://hacker-news.firebaseio.com/v0/topstories.json');
    const topIds = (await topIdsRes.json()).slice(0, 20); // Get top 20 for testing

    for (const id of topIds) {
        // 2. Fetch story details
        const storyRes = await fetch(`https://hacker-news.firebaseio.com/v0/item/${id}.json`);
        const story = await storyRes.json();
        
        if (story.title) {
            console.log(`📝 Processing: ${story.title}`);
            
            // 3. Vectorize the title
            const vectorArray = await getEmbedding(story.title);
            
            // 4. Save to Redis Hash (matches the index prefix 'hn:')
            await redis.hSet(`hn:${id}`, {
                title: story.title,
                content: story.url || "No URL provided",
                vector: Buffer.from(new Float32Array(vectorArray).buffer)
            });
        }
    }

    console.log("✅ Ingestion complete!");
    process.exit();
}

runIngestion().catch(console.error);