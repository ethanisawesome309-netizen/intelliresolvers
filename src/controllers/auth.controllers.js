const jwt = require("jsonwebtoken");
const User = require("../models/user.model");
const { hashPassword, verifyPassword } = require("../utils/password");

exports.register = async (req, res) => {
  const { email, password } = req.body;

  const existing = await User.findByEmail(email);
  if (existing) return res.status(400).json({ message: "User exists" });

  const passwordHash = await hashPassword(password);
  await User.createUser(email, passwordHash);

  res.status(201).json({ message: "User registered" });
};

exports.login = async (req, res) => {
  const { email, password } = req.body;

  const user = await User.findByEmail(email);
  if (!user) return res.status(401).json({ message: "Invalid credentials" });

  const valid = await verifyPassword(password, user.password_hash);
  if (!valid) return res.status(401).json({ message: "Invalid credentials" });

  const token = jwt.sign(
    { userId: user.id },
    process.env.JWT_SECRET,
    { expiresIn: "1h" }
  );

  res.json({ token });
};
