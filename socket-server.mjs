import http from "http";
import { Server } from "socket.io";
import { createClient } from "redis";

const PORT = process.env.PORT || 3001; 

const httpServer = http.createServer();

const io = new Server(httpServer, {
  path: "/socket.io/", // Match the trailing slash in Nginx
  cors: {
    // Add your exact domain here. If you use https://intelliresolvers.com, it must be here.
    origin: [
      "https://intelliresolvers.com", 
      "https://www.intelliresolvers.com", 
      "https://intelliresolvers.azurewebsites.net"
    ],
    methods: ["GET", "POST"],
    credentials: true
  },
  // High performance settings for Azure
  transports: ['websocket', 'polling'] 
});

const redis = createClient({
  socket: {
    host: "127.0.0.1",
    port: 6379
  }
});

redis.on("error", err => console.error("❌ Redis error:", err));

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

httpServer.listen(PORT, "0.0.0.0", () => {
  console.log(`🚀 Socket.IO bridge active on port ${PORT}`);
});