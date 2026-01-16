import { Server } from "socket.io";
import { createClient } from "redis";

const io = new Server(3001, {
  cors: { 
    origin: "https://www.intelliresolvers.com", 
    credentials: true 
  }
});

const sub = createClient();
await sub.connect();
console.log("✅ Node connected to Memurai. Listening for ticket_updates...");

sub.subscribe("ticket_updates", (message) => {
  console.log("📩 RECEIVED FROM REDIS:", message);
  
  // Forward to all browser windows
  io.emit("refresh_tickets", JSON.parse(message));
  
  console.log("🚀 BROADCASTED to browsers.");
});

io.on("connection", (socket) => {
  console.log("👤 Browser connected:", socket.id);
});