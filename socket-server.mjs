import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";
import fs from "fs/promises";
import path from "path";
import { fileURLToPath } from "url"; // Required for absolute pathing
import { GoogleGenerativeAI } from "@google/generative-ai";

// --- ABSOLUTE PATH FIX FOR COMMONJS LIBRARIES ---
import { createRequire } from "module";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// This forces require to look EXACTLY in /home/site/wwwroot/node_modules
const require = createRequire(path.join(__dirname, "node_modules/"));

const pdf = require("pdf-extraction"); 
const mammoth = require("mammoth");

// --- AI CONFIGURATION (Updated for Jan 2026) ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash" }); 
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

// --- NEW: RETRY HELPER FOR QUOTA ERRORS ---
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

  // --- HN BOT LISTENER (With Retry Logic) ---
  socket.on("ask_hn_bot", async (query) => {
    console.log(`🔎 HN Bot Request: ${query}`);
    try {
      const answer = await withRetry(async () => {
        const prompt = `You are a tech trend expert. Based on recent Hacker News discussions, answer concisely: ${query}`;
        const result = await model.generateContent(prompt);
        return result.response.text();
      });

      socket.emit("hn_bot_response", answer);
    } catch (err) {
      console.error("❌ HN Bot Error:", err);
      // Send a clean error message back to the UI to stop the spinner
      const errorMsg = err.status === 429 
        ? "⚠️ AI is currently at its limit. Please try again in 1 minute." 
        : "Sorry, I couldn't fetch HN trends at the moment.";
      socket.emit("hn_bot_response", errorMsg);
    }
  });

  socket.on("request_summary", async ({ ticketId, filePath }) => {
    console.log(`✨ Summarizing file for Ticket #${ticketId}: ${filePath}`);
    
    try {
      const fullPath = path.resolve('/home/site/wwwroot', filePath);
      const ext = path.extname(fullPath).toLowerCase();
      let aiResponse = "";

      if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
        const imageData = await fs.readFile(fullPath);
        const result = await model.generateContent([
          "Analyze this screenshot from a support ticket. Identify any error messages or technical issues visible.",
          { inlineData: { data: imageData.toString("base64"), mimeType: `image/${ext.replace('.','')}` } }
        ]);
        aiResponse = result.response.text();
      } 
      else if (ext === '.pdf') {
        const dataBuffer = await fs.readFile(fullPath);
        const data = await pdf(dataBuffer); 
        const result = await model.generateContent(`Summarize the following support document: ${data.text}`);
        aiResponse = result.response.text();
      }
      else if (ext === '.docx') {
        const data = await mammoth.extractRawText({ path: fullPath });
        const result = await model.generateContent(`Summarize the following support document: ${data.value}`);
        aiResponse = result.response.text();
      } else {
        aiResponse = "Unsupported file format for AI analysis.";
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