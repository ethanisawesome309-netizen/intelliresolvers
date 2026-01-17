import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";

const PORT = process.env.PORT; // REQUIRED on Azure

if (!PORT) {
  console.error("❌ process.env.PORT not set");
  process.exit(1);
}

// 1️⃣ HTTP server (Azure proxy requires this)
const httpServer = http.createServer();

// 2️⃣ Socket.IO attached to HTTP server
const io = new Server(httpServer, {
  path: "/socket.io",
  cors: {
    origin: "https://www.intelliresolvers.com",
    credentials: true
  }
});

// 3️⃣ Redis subscriber (Memurai)
const redis = createClient({
  socket: {
    host: "127.0.0.1",
    port: 6379
  }
});

redis.on("error", err => {
  console.error("❌ Redis error:", err);
});

await redis.connect();
console.log("✅ Connected to Redis. Listening for ticket_updates...");

// 4️⃣ Listen for PHP events
await redis.subscribe("ticket_updates", (message) => {
  try {
    const payload = JSON.parse(message);
    io.emit("refresh_tickets", payload);
    console.log("🚀 Broadcasted update:", payload.ticket_id);
  } catch (e) {
    console.error("❌ Invalid Redis message:", message);
  }
});

// 5️⃣ Browser connections
io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);

  socket.on("disconnect", () => {
    console.log("👋 Browser disconnected:", socket.id);
  });
});

// Replace your Step 6 with this:
const INTERNAL_NODE_PORT = 3001; 
httpServer.listen(INTERNAL_NODE_PORT, () => {
  console.log(`🚀 Socket.IO internal bridge running on port ${INTERNAL_NODE_PORT}`);
});