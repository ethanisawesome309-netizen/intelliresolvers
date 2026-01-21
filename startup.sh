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
echo "Node installed at: $NODE_PATH (Version: $(node -v))"

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
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    nginx -t && service nginx reload
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot

# Ensure we have the correct path for npm
export PATH=$PATH:/usr/bin:/usr/local/bin
NODE_EXE=$(which node)

# Clean up any previous failed runs
pkill -f socket-server.mjs || true

# Re-install modules if missing
if [ ! -d "node_modules" ]; then
    echo "node_modules not found, installing..."
    npm install --production
fi

echo "Launching Node.js Bridge on Port 3001..."
# Redirect both stdout and stderr to the log file
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS ---
echo "Finalizing permissions..."
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot

# --- 7. START PHP-FPM ---
echo "🚀 Starting PHP-FPM..."
# Using exec to replace the shell as the primary container process
exec php-fpm -F -R -d "listen=127.0.0.1:9000"