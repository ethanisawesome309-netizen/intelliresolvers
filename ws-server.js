const io = require("socket.io")(3001, {
  cors: { origin: "http://localhost:3000", credentials: true }
});
const redis = require("redis");

const sub = redis.createClient();

(async () => {
  await sub.connect();
  console.log("Redis connected. Monitoring channel: ticket_updates");

  await sub.subscribe("ticket_updates", (message) => {
    console.log("Broadcasting update:", message);
    // Broadcast to all connected React clients
    io.emit("refresh_tickets", JSON.parse(message));
  });
})();

io.on("connection", (socket) => {
  console.log("User connected to WebSockets:", socket.id);
});