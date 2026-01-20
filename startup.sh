#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
# Removed 'set -e' to prevent the script from dying if one small command fails
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCY INSTALLATION ---
# Check if Node is already there to avoid redundant 'apt' locks
if ! command -v node &> /dev/null; then
    echo "Installing Node 18 and Redis..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get update
    apt-get install -y nodejs redis-server --no-install-recommends
fi

# --- 3. SERVICE: REDIS ---
echo "Starting Redis..."
# Ensure redis-server is in the path and start it
service redis-server start || redis-server --daemonize yes

# Wait for Redis to be ready
COUNT=0
while ! redis-cli ping | grep -q PONG && [ $COUNT -lt 5 ]; do
    echo "Waiting for Redis..."
    sleep 2
    ((COUNT++))
done

# --- 4. NGINX CONFIGURATION ---
echo "Syncing Nginx configurations..."
if [ -f /home/site/wwwroot/default.txt ]; then
    # Overwrite both to be safe
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-enabled/default
    
    if nginx -t; then
        service nginx reload
    fi
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot
# Install dependencies if they are missing
if [ ! -d "node_modules" ]; then
    npm install --production
fi

# Kill old ghost processes
pkill node || true

export PORT=3001
echo "Launching Node.js..."
# Use absolute path to node to be safe
nohup /usr/bin/node socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS ---
echo "Setting permissions..."
# In your image, PHP-FPM uses port 9000, but we create the dir just in case
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php

# --- 7. FINAL HANDOFF: PHP-FPM ---
echo "🚀 Starting PHP-FPM..."
# IMPORTANT: Added -F to keep container alive and -R to allow root start if needed
exec php-fpm -F -R