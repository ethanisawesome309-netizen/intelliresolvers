import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";
import fs from "fs/promises";
import path from "path";
import { fileURLToPath } from "url";
import { GoogleGenerativeAI } from "@google/generative-ai";
import Groq from "groq-sdk";

// --- NEW: Postgres for AI Memory ---
import pg from "pg";
import { RecursiveCharacterTextSplitter } from "@langchain/textsplitters";

// --- ABSOLUTE PATH FIX ---
import { createRequire } from "module";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const require = createRequire(path.join(__dirname, "node_modules/"));

const pdf = require("pdf-extraction"); 
const mammoth = require("mammoth");

// --- AI CONFIGURATION ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash" }); 
// Embeddings model for Vector Search
const embedModel = genAI.getGenerativeModel({ model: "text-embedding-004" });

const groq = new Groq({ apiKey: process.env.GROQ_API_KEY });

// --- NEW: Postgres Pool (Neon) ---
const pool = new pg.Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false } // Required for Neon
});

// --- NEW: Text Splitter (Chunks for Memory) ---
const splitter = new RecursiveCharacterTextSplitter({
    chunkSize: 1000,
    chunkOverlap: 200,
});

const PORT = 3001; 
const httpServer = http.createServer();

// ... [Keep your existing io/Server and withRetry logic exactly the same] ...

const io = new Server(httpServer, {
  path: "/socket.io/",
  cors: {
    origin: ["https://intelliresolvers.com", "https://www.intelliresolvers.com", "https://intelliresolvers.azurewebsites.net"],
    methods: ["GET", "POST"],
    credentials: true
  },
  transports: ['websocket', 'polling'] 
});

const withRetry = async (fn, maxRetries = 3, baseDelay = 5000) => {
  for (let i = 0; i < maxRetries; i++) {
    try { return await fn(); } catch (err) {
      const isRateLimit = err.status === 429 || err.message?.includes("429");
      if (isRateLimit && i < maxRetries - 1) {
        const delay = baseDelay * Math.pow(2, i); 
        await new Promise(res => setTimeout(res, delay));
        continue;
      }
      throw err;
    }
  }
};

// --- REDIS SETUP (UNCHANGED) ---
const redis = createClient({ socket: { host: "127.0.0.1", port: 6379 } });
await redis.connect();
console.log("✅ Connected to Redis. Listening for ticket_updates...");

await redis.subscribe("ticket_updates", (message) => {
  try {
    const payload = JSON.parse(message);
    io.emit("refresh_tickets", payload);
  } catch (e) { console.error("❌ Invalid Redis message:", message); }
});

// --- SOCKET LOGIC ---
io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);

  // ... [Keep your HN Bot listener exactly as is] ...

  socket.on("request_summary", async ({ ticketId, filePath }) => {
    console.log(`✨ Summarizing file for Ticket #${ticketId}: ${filePath}`);
    try {
      const fullPath = path.resolve('/home/site/wwwroot', filePath);
      const ext = path.extname(fullPath).toLowerCase();
      let aiResponse = "";
      let textToLearn = ""; // Store raw text for Vector Memory

      if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
        const imageData = await fs.readFile(fullPath);
        const result = await model.generateContent([
          "Analyze this screenshot from a support ticket. Identify any error messages or technical issues visible.",
          { inlineData: { data: imageData.toString("base64"), mimeType: `image/${ext.replace('.','')}` } }
        ]);
        aiResponse = result.response.text();
        textToLearn = aiResponse; // Use the AI's description of the image as the "memory"
      } 
      else if (ext === '.pdf') {
        const dataBuffer = await fs.readFile(fullPath);
        const data = await pdf(dataBuffer); 
        textToLearn = data.text;
        const result = await model.generateContent(`Summarize the following support document: ${data.text}`);
        aiResponse = result.response.text();
      }
      else if (ext === '.docx') {
        const data = await mammoth.extractRawText({ path: fullPath });
        textToLearn = data.value;
        const result = await model.generateContent(`Summarize the following support document: ${data.value}`);
        aiResponse = result.response.text();
      }

      // --- NEW: Save to Neon Vector Memory ---
      if (textToLearn) {
        try {
          const chunks = await splitter.splitText(textToLearn);
          for (const chunk of chunks) {
            const emb = await embedModel.embedContent(chunk);
            const vector = emb.embedding.values;
            
            await pool.query(
              'INSERT INTO ticket_knowledge (ticket_id, content, embedding) VALUES ($1, $2, $3)',
              [ticketId, chunk, JSON.stringify(vector)]
            );
          }
          console.log(`🧠 Memorized ${chunks.length} chunks from Ticket #${ticketId}`);
        } catch (dbErr) {
          console.error("❌ Failed to save to Neon memory:", dbErr.message);
        }
      }

      socket.emit("summary_ready", { ticketId, summary: aiResponse });

    } catch (err) {
      console.error("❌ AI Error:", err);
      socket.emit("summary_ready", { ticketId, summary: "Error: Could not process file." });
    }
  });

  socket.on("disconnect", () => console.log("👋 Browser disconnected"));
});

httpServer.listen(PORT, "0.0.0.0", () => {
  console.log(`🚀 Socket.IO bridge active on port ${PORT}`);
});