#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCY INSTALLATION ---
if ! command -v node &> /dev/null; then
    apt-get update && apt-get install -y curl
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

# --- 3. SERVICE: REDIS ---
service redis-server start || redis-server --daemonize yes

# --- 4. NGINX CONFIGURATION ---
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    # Use restart instead of reload to force-clear broken WebSocket tunnels
    service nginx restart
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
cd /home/site/wwwroot
NODE_EXE=$(which node)
pkill -f socket-server.mjs || true
# Use nohup to ensure the bridge stays alive
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES (Added for Uploads) ---
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot
chmod -R 777 /home/site/wwwroot/uploads

# --- 7. START PHP-FPM (YOUR WORKING COMMAND) ---
pkill -f php-fpm || true
php-fpm -F -R -d "listen=127.0.0.1:9000"