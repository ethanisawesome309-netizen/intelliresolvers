#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCY INSTALLATION (Node 18 & Redis) ---
if ! command -v node &> /dev/null; then
    echo "Node.js not found. Starting installation..."
    apt-get update && apt-get install -y curl
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

NODE_PATH=$(which node)
if [ -z "$NODE_PATH" ]; then
    echo "ERROR: Node installation failed."
else
    echo "Node installed at: $NODE_PATH (Version: $(node -v))"
fi

# --- 3. SERVICE: REDIS ---
echo "Starting Redis..."
service redis-server start || redis-server --daemonize yes

COUNT=0
while ! redis-cli ping | grep -q PONG && [ $COUNT -lt 5 ]; do
    echo "Waiting for Redis..."
    sleep 2
    ((COUNT++))
done

# --- 4. NGINX CONFIGURATION ---
echo "Syncing Nginx configurations..."
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    # We move the reload to the end of the script to ensure 404s don't occur during permission sync
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot
export PATH=$PATH:/usr/bin:/usr/local/bin
NODE_EXE=$(which node)

if [ -z "$NODE_EXE" ]; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
    NODE_EXE="/usr/bin/node"
fi

if [ ! -d "node_modules" ]; then
    $NODE_EXE /usr/bin/npm install --production
fi

pkill -f socket-server.mjs || true
export PORT=3001 
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES ---
echo "Finalizing permissions..."
# ✅ ADDED: Ensure the uploads folder exists for the new feature
mkdir -p /home/site/wwwroot/uploads/tickets

mkdir -p /var/run/php
mkdir -p /var/log/php-fpm

# Ensure permissions are correct BEFORE Nginx reloads
chown -R www-data:www-data /home/site/wwwroot /var/run/php /var/log/php-fpm
chmod -R 755 /home/site/wwwroot

# ✅ ADDED: Reload Nginx now that permissions are set
if nginx -t; then
    service nginx reload
fi

# --- 7. START PHP-FPM ---
echo "🚀 Starting PHP-FPM..."
pkill -f php-fpm || true
php-fpm -F -R -d "listen=127.0.0.1:9000"