import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";
import fs from "fs/promises";
import path from "path";
import { fileURLToPath } from "url";
import { GoogleGenerativeAI } from "@google/generative-ai";
import Groq from "groq-sdk"; // ADDED: Groq for fallback
import pg from "pg"; // ADDED: Postgres for Memory
import { RecursiveCharacterTextSplitter } from "@langchain/textsplitters";

// --- ABSOLUTE PATH FIX ---
import { createRequire } from "module";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const require = createRequire(path.join(__dirname, "node_modules/"));

const pdf = require("pdf-extraction"); 
const mammoth = require("mammoth");

// --- AI & DB CONFIGURATION ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash" }); 
const embedModel = genAI.getGenerativeModel({ model: "text-embedding-004" });
const groq = new Groq({ apiKey: process.env.GROQ_API_KEY });

// Neon Postgres Connection
const pool = new pg.Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

const splitter = new RecursiveCharacterTextSplitter({ chunkSize: 1000, chunkOverlap: 200 });

// --- IMPROVED RETRY HELPER ---
const withRetry = async (fn, maxRetries = 2) => {
    for (let i = 0; i < maxRetries; i++) {
        try { return await fn(); } catch (err) {
            const isRateLimit = err.status === 429 || err.message?.includes("429");
            if (isRateLimit && i < maxRetries - 1) {
                const delay = 2000 * Math.pow(2, i); 
                await new Promise(res => setTimeout(res, delay));
                continue;
            }
            throw err;
        }
    }
};

const httpServer = http.createServer();
const io = new Server(httpServer, {
    path: "/socket.io/",
    cors: { origin: "*", methods: ["GET", "POST"] }
});

// --- REDIS SETUP (Unchanged) ---
const redis = createClient({ socket: { host: "127.0.0.1", port: 6379 } });
await redis.connect();
await redis.subscribe("ticket_updates", (msg) => io.emit("refresh_tickets", JSON.parse(msg)));

// --- SOCKET LOGIC ---
io.on("connection", (socket) => {
    // HN Bot with Groq Fallback
    socket.on("ask_hn_bot", async (query) => {
        const prompt = `Tech trend expert answer: ${query}`;
        try {
            // Attempt Gemini
            const answer = await withRetry(() => model.generateContent(prompt).then(r => r.response.text()));
            socket.emit("hn_bot_response", answer);
        } catch (err) {
            console.warn("⚠️ Gemini Quota Exceeded. Falling back to Groq...");
            try {
                const chat = await groq.chat.completions.create({
                    messages: [{ role: "user", content: prompt }],
                    model: "llama-3.3-70b-versatile",
                });
                socket.emit("hn_bot_response", chat.choices[0].message.content);
            } catch (groqErr) {
                socket.emit("hn_bot_response", "AI services are busy. Try again later.");
            }
        }
    });

    // Summary with Neon Memory & Fallback
    socket.on("request_summary", async ({ ticketId, filePath }) => {
        try {
            const fullPath = path.resolve('/home/site/wwwroot', filePath);
            const ext = path.extname(fullPath).toLowerCase();
            let rawText = "";
            let summary = "";

            // 1. Check if we already have a summary in Postgres to save API calls
            try {
                const existing = await pool.query('SELECT summary FROM ticket_summaries WHERE ticket_id = $1', [ticketId]);
                if (existing.rows.length > 0) {
                    return socket.emit("summary_ready", { ticketId, summary: existing.rows[0].summary });
                }
            } catch (dbErr) {
                console.error("⚠️ Database check failed, continuing to AI...", dbErr.message);
            }

            // 2. Extract Text (Preserved Logic)
            if (ext === '.pdf') {
                rawText = (await pdf(await fs.readFile(fullPath))).text;
            } else if (ext === '.docx') {
                rawText = (await mammoth.extractRawText({ path: fullPath })).value;
            } else if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
                const img = await fs.readFile(fullPath);
                const res = await model.generateContent([
                    "Describe errors in this image:",
                    { inlineData: { data: img.toString("base64"), mimeType: `image/${ext.slice(1)}` } }
                ]);
                rawText = res.response.text();
            }

            // 3. Generate Summary with Fallback
            try {
                summary = await withRetry(() => model.generateContent(`Summarize: ${rawText}`).then(r => r.response.text()));
            } catch (err) {
                const chat = await groq.chat.completions.create({
                    messages: [{ role: "user", content: `Summarize: ${rawText}` }],
                    model: "llama-3.3-70b-versatile",
                });
                summary = chat.choices[0].message.content;
            }

            // 4. Save to Neon Memory
            try {
                await pool.query('INSERT INTO ticket_summaries (ticket_id, summary) VALUES ($1, $2) ON CONFLICT (ticket_id) DO NOTHING', [ticketId, summary]);
            } catch (saveErr) {
                console.error("⚠️ Failed to save summary to Neon:", saveErr.message);
            }

            socket.emit("summary_ready", { ticketId, summary });
        } catch (err) {
            console.error("❌ Summary Error:", err);
            socket.emit("summary_ready", { ticketId, summary: "Error: AI services busy." });
        }
    });
});

httpServer.listen(3001, "0.0.0.0", () => {
    console.log(`🚀 Socket.IO bridge active on port 3001`);
});