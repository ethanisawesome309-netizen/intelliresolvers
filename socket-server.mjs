import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";

// Use 3001 as fallback if PORT isn't passed
const PORT = process.env.PORT || 3001; 

// 1️⃣ HTTP server 
const httpServer = http.createServer();

// 2️⃣ Socket.IO attached to HTTP server
const io = new Server(httpServer, {
  path: "/socket.io",
  cors: {
    origin: ["https://www.intelliresolvers.com", "https://intelliresolvers.azurewebsites.net"],
    credentials: true
  }
});

// 3️⃣ Redis subscriber
const redis = createClient({
  socket: {
    host: "127.0.0.1",
    port: 6379
  }
});

redis.on("error", err => console.error("❌ Redis error:", err));

await redis.connect();
console.log("✅ Connected to Redis. Listening for ticket_updates...");

// 4️⃣ Listen for PHP events via Redis
await redis.subscribe("ticket_updates", (message) => {
  try {
    const payload = JSON.parse(message);
    io.emit("refresh_tickets", payload);
    console.log("🚀 Broadcasted update for ticket:", payload.ticket_id);
  } catch (e) {
    console.error("❌ Invalid Redis message:", message);
  }
});

// 5️⃣ Browser connections
io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);
  socket.on("disconnect", () => console.log("👋 Browser disconnected"));
});

httpServer.listen(PORT, () => {
  console.log(`🚀 Socket.IO bridge active on port ${PORT}`);
});