import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";

// FORCE port 3001 to avoid conflict with Nginx (8080)
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

const redis = createClient({
  socket: {
    host: "127.0.0.1",
    port: 6379
  }
});

redis.on("error", err => console.error("❌ Redis error:", err));

// Connect to Redis before subscribing
await redis.connect();
console.log("✅ Connected to Redis. Listening for ticket_updates...");

await redis.subscribe("ticket_updates", (message) => {
  try {
    const payload = JSON.parse(message);
    io.emit("refresh_tickets", payload);
    console.log("🚀 Broadcasted update for ticket:", payload.ticket_id);
  } catch (e) {
    console.error("❌ Invalid Redis message:", message);
  }
});

io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);
  socket.on("disconnect", () => console.log("👋 Browser disconnected"));
});

// Explicitly bind to 0.0.0.0
httpServer.listen(PORT, "0.0.0.0", () => {
  console.log(`🚀 Socket.IO bridge active on port ${PORT}`);
});