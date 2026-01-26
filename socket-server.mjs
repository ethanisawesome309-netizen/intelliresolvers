import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";
import fs from "fs/promises";
import path from "path";
import { GoogleGenerativeAI } from "@google/generative-ai";

// --- FIX FOR COMMONJS LIBRARIES (Node 18 ESM Compatibility) ---
import { createRequire } from "module";
const require = createRequire(import.meta.url);

// Use pdf-extraction to avoid DOMMatrix/browser errors in Node
const pdf = require("pdf-extraction"); 
const mammoth = require("mammoth");

// --- AI CONFIGURATION ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

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

  socket.on("request_summary", async ({ ticketId, filePath }) => {
    console.log(`✨ Summarizing file for Ticket #${ticketId}: ${filePath}`);
    
    try {
      const fullPath = path.resolve('/home/site/wwwroot', filePath);
      const ext = path.extname(fullPath).toLowerCase();
      let aiResponse = "";

      // 1. Handle Images (Screenshots)
      if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
        const imageData = await fs.readFile(fullPath);
        const result = await model.generateContent([
          "Analyze this screenshot from a support ticket. Identify any error messages or technical issues visible.",
          { inlineData: { data: imageData.toString("base64"), mimeType: `image/${ext.replace('.','')}` } }
        ]);
        aiResponse = result.response.text();
      } 
      // 2. Handle PDFs
      else if (ext === '.pdf') {
        const dataBuffer = await fs.readFile(fullPath);
        const data = await pdf(dataBuffer); 
        const result = await model.generateContent(`Summarize the following support document: ${data.text}`);
        aiResponse = result.response.text();
      }
      // 3. Handle Word Docs
      else if (ext === '.docx') {
        const data = await mammoth.extractRawText({ path: fullPath });
        const result = await model.generateContent(`Summarize the following support document: ${data.value}`);
        aiResponse = result.response.text();
      } else {
        aiResponse = "Unsupported file format for AI analysis.";
      }

      // Send the summary back to the specific requester
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