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
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    nginx -t && service nginx reload
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot

# Ensure npm is present and install dependencies
if [ -f "package.json" ]; then
    npm install --production
fi

# Kill old ghost processes
pkill node || true

export PORT=3001
echo "Launching Node.js Bridge..."
# Use the full path found earlier to ensure it runs
nohup $NODE_PATH socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS ---
echo "Finalizing permissions..."
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot

# --- 7. START PHP-FPM ---
echo "🚀 Starting PHP-FPM on 127.0.0.1:9000..."
exec php-fpm -d "listen=127.0.0.1:9000" -F -R