#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCY INSTALLATION (Node 18 & Redis) ---
if ! command -v node &> /dev/null; then
    echo "Node.js not found. Starting installation..."
    # Ensure curl is available
    apt-get update && apt-get install -y curl
    # Force NodeSource 18.x setup
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    # Refresh the hash to find the new executable
    hash -r 
fi

# Double check installation success
NODE_PATH=$(which node)
if [ -z "$NODE_PATH" ]; then
    echo "ERROR: Node installation failed."
else
    echo "Node installed at: $NODE_PATH (Version: $(node -v))"
fi

# --- 3. SERVICE: REDIS ---
echo "Starting Redis..."
service redis-server start || redis-server --daemonize yes

# Wait for Redis (max 10 seconds)
COUNT=0
while ! redis-cli ping | grep -q PONG && [ $COUNT -lt 5 ]; do
    echo "Waiting for Redis..."
    sleep 2
    ((COUNT++))
done

# --- 4. NGINX CONFIGURATION ---
echo "Syncing Nginx configurations..."
if [ -f /home/site/wwwroot/default.txt ]; then
    # REVERTED TO YOUR ORIGINAL LOGIC
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    if nginx -t; then
        service nginx reload
    fi
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot

# Ensure the system looks in common Node installation folders
export PATH=$PATH:/usr/bin:/usr/local/bin

# Find the absolute path to node
NODE_EXE=$(which node)

if [ -z "$NODE_EXE" ]; then
    echo "Node not found in PATH, trying manual install..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
    NODE_EXE="/usr/bin/node"
fi

echo "Using Node from: $NODE_EXE"

# Install dependencies if node_modules is missing
if [ ! -d "node_modules" ]; then
    $NODE_EXE /usr/bin/npm install --production
fi

pkill -f socket-server.mjs || true

# --- CRITICAL FIX: Decouple Port from Nginx ---
export PORT=3001 
echo "Launching Node.js Bridge on PORT 3001..."
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES ---
echo "Finalizing permissions..."
# PHP-FPM NEEDS this directory to start, even if using 127.0.0.1
mkdir -p /var/run/php
mkdir -p /var/log/php-fpm
chown -R www-data:www-data /home/site/wwwroot /var/run/php /var/log/php-fpm
chmod -R 755 /home/site/wwwroot

# --- 7. START PHP-FPM ---
echo "🚀 Starting PHP-FPM..."
mkdir -p /var/run/php

# Kill any ghost processes
pkill -f php-fpm || true

# Start PHP-FPM in the foreground as the final command 
# This keeps the Azure container alive.
php-fpm -F -R -d "listen=127.0.0.1:9000"