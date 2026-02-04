import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";
import fs from "fs/promises";
import path from "path";
import { fileURLToPath } from "url";
import { GoogleGenerativeAI } from "@google/generative-ai";
// ADDED: Groq SDK
import Groq from "groq-sdk";
// ADDED FOR NEON/AI MEMORY:
import pg from 'pg';
import { RecursiveCharacterTextSplitter } from "@langchain/textsplitters";

// --- ABSOLUTE PATH FIX FOR COMMONJS LIBRARIES ---
import { createRequire } from "module";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const require = createRequire(path.join(__dirname, "node_modules/"));

const pdf = require("pdf-extraction"); 
const mammoth = require("mammoth");

// --- AI CONFIGURATION ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash" }); 
// ADDED: Embedding model for Vector Search
const embedModel = genAI.getGenerativeModel({ model: "text-embedding-004" });

// ADDED: Initialize Groq
const groq = new Groq({ apiKey: process.env.GROQ_API_KEY });

// ADDED: Neon Postgres Connection
const pool = new pg.Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

// ADDED: Text Splitter for handling large documents
const splitter = new RecursiveCharacterTextSplitter({
    chunkSize: 1000,
    chunkOverlap: 200,
});

const PORT = 3001; 
const httpServer = http.createServer();

const io = new Server(httpServer, {
  path: "/socket.io/",
  cors: {
    origin: [
      "https://intelliresolvers.com", 
      "https://www.intelliresolvers.com", 
      "https://intelliresolvers.azurewebsites.net"
    ],
    methods: ["GET", "POST"],
    credentials: true
  },
  transports: ['websocket', 'polling'] 
});

// --- RETRY HELPER FOR QUOTA ERRORS ---
const withRetry = async (fn, maxRetries = 3, baseDelay = 5000) => {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn();
    } catch (err) {
      const isRateLimit = err.status === 429 || err.message?.includes("429");
      if (isRateLimit && i < maxRetries - 1) {
        const delay = baseDelay * Math.pow(2, i); 
        console.warn(`⚠️ Rate limited. Retrying in ${delay}ms... (Attempt ${i + 1}/${maxRetries})`);
        await new Promise(res => setTimeout(res, delay));
        continue;
      }
      throw err;
    }
  }
};

// --- REDIS SETUP ---
const redis = createClient({ socket: { host: "127.0.0.1", port: 6379 } });
redis.on("error", err => console.error("❌ Redis error:", err));
await redis.connect();
console.log("✅ Connected to Redis. Listening for ticket_updates...");

await redis.subscribe("ticket_updates", (message) => {
  try {
    const payload = JSON.parse(message);
    io.emit("refresh_tickets", payload);
  } catch (e) {
    console.error("❌ Invalid Redis message:", message);
  }
});

// --- SOCKET LOGIC ---
io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);

  // --- HN BOT LISTENER (With Dual-AI Fallback & Vector Memory Search) ---
  socket.on("ask_hn_bot", async (query) => {
    console.log(`🔎 HN Bot Request: ${query}`);
    
    // START ADDED: SEARCH NEON MEMORY
    let contextString = "";
    try {
        const embRes = await embedModel.embedContent(query);
        const queryVector = JSON.stringify(embRes.embedding.values);
        
        const { rows } = await pool.query(
            `SELECT content FROM ticket_knowledge 
             ORDER BY embedding <=> $1::vector 
             LIMIT 3`, 
            [queryVector]
        );
        if (rows.length > 0) {
            contextString = "\nRelevant past ticket info:\n" + rows.map(r => r.content).join("\n---\n");
        }
    } catch (dbErr) {
        console.error("❌ Memory Retrieval Error:", dbErr);
    }
    // END ADDED

    const prompt = `You are a tech trend expert. ${contextString} Based on context and recent Hacker News discussions, answer concisely: ${query}`;
    
    try {
      // Step 1: Try Gemini with Retries
      const answer = await withRetry(async () => {
        const result = await model.generateContent(prompt);
        return result.response.text();
      });
      socket.emit("hn_bot_response", answer);

    } catch (err) {
      // Step 2: Fallback to Groq if Gemini is truly exhausted (429)
      if (err.status === 429 || err.message?.includes("429")) {
        console.warn("⚠️ Gemini exhausted. Switching to Groq/Llama...");
        try {
          const chatCompletion = await groq.chat.completions.create({
            messages: [{ role: "user", content: prompt }],
            model: "llama-3.3-70b-versatile",
          });
          const groqAnswer = chatCompletion.choices[0].message.content;
          socket.emit("hn_bot_response", groqAnswer);
        } catch (groqErr) {
          console.error("❌ Both AI services failed:", groqErr);
          socket.emit("hn_bot_response", "⚠️ AI services are currently busy. Please try again later.");
        }
      } else {
        console.error("❌ HN Bot Error:", err);
        socket.emit("hn_bot_response", "Sorry, I couldn't fetch HN trends at the moment.");
      }
    }
  });

  socket.on("request_summary", async ({ ticketId, filePath }) => {
    console.log(`✨ Summarizing file for Ticket #${ticketId}: ${filePath}`);
    try {
      const fullPath = path.resolve('/home/site/wwwroot', filePath);
      const ext = path.extname(fullPath).toLowerCase();
      let aiResponse = "";
      let textToLearn = ""; // ADDED: Variable to hold text for vector storage

      if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
        const imageData = await fs.readFile(fullPath);
        const result = await model.generateContent([
          "Analyze this screenshot from a support ticket. Identify any error messages or technical issues visible.",
          { inlineData: { data: imageData.toString("base64"), mimeType: `image/${ext.replace('.','')}` } }
        ]);
        aiResponse = result.response.text();
        textToLearn = aiResponse; // Use the AI's analysis of the image as memory
      } 
      else if (ext === '.pdf') {
        const dataBuffer = await fs.readFile(fullPath);
        const data = await pdf(dataBuffer); 
        textToLearn = data.text; // Store raw PDF text
        const result = await model.generateContent(`Summarize the following support document: ${data.text}`);
        aiResponse = result.response.text();
      }
      else if (ext === '.docx') {
        const data = await mammoth.extractRawText({ path: fullPath });
        textToLearn = data.value; // Store raw Docx text
        const result = await model.generateContent(`Summarize the following support document: ${data.value}`);
        aiResponse = result.response.text();
      } else {
        aiResponse = "Unsupported file format for AI analysis.";
      }

      // START ADDED: SAVE TO NEON AI MEMORY
      if (textToLearn && textToLearn.length > 50) {
          const chunks = await splitter.splitText(textToLearn);
          for (const chunk of chunks) {
              const emb = await embedModel.embedContent(chunk);
              await pool.query(
                  'INSERT INTO ticket_knowledge (ticket_id, content, embedding) VALUES ($1, $2, $3)',
                  [ticketId, chunk, JSON.stringify(emb.embedding.values)]
              );
          }
          console.log(`🧠 Memorized ${chunks.length} chunks from Ticket #${ticketId}`);
      }
      // END ADDED

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