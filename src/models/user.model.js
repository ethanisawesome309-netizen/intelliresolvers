const db = require("../db");

async function findByEmail(email) {
  const [rows] = await db.query(
    "SELECT * FROM users WHERE email = ?",
    [email]
  );
  return rows[0];
}

async function createUser(email, passwordHash) {
  await db.query(
    "INSERT INTO users (email, password_hash) VALUES (?, ?)",
    [email, passwordHash]
  );
}

module.exports = { findByEmail, createUser };
