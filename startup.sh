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
echo "Node installed at: $NODE_PATH"

# --- 3. SERVICE: REDIS ---
echo "Starting Redis..."
service redis-server start || redis-server --daemonize yes

# --- 4. PREPARE APP ---
cd /home/site/wwwroot
NODE_EXE=$(which node)

if [ ! -d "node_modules" ]; then
    echo "Installing node_modules..."
    $NODE_EXE /usr/bin/npm install --production
fi

pkill -f socket-server.mjs || true
export PORT=3001 
echo "Launching Node.js Bridge..."
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 5. PERMISSIONS & DIRECTORY SETUP ---
echo "Fixing permissions and folders..."
# Create the uploads folder required for the new ticket feature
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php
mkdir -p /var/log/php-fpm

# Apply ownership to web user (www-data)
chown -R www-data:www-data /home/site/wwwroot /var/run/php /var/log/php-fpm
chmod -R 755 /home/site/wwwroot

# --- 6. NGINX SYNC (The 404 Killer) ---
echo "Cleaning conflicting Nginx configs..."
# ✅ FIX: Remove the default configs that were pointing to /html/
rm -rf /etc/nginx/sites-enabled/*
rm -rf /etc/nginx/sites-available/default

if [ -f /home/site/wwwroot/default.txt ]; then
    echo "Applying custom default.txt..."
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    # ✅ FIX: Explicitly link to sites-enabled to ensure it's active
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    
    if nginx -t; then
        # Force restart instead of reload to clear cached 404s
        service nginx restart
    fi
fi

# --- 7. START PHP ---
echo "🚀 Starting PHP-FPM..."
pkill -f php-fpm || true

# Start PHP-FPM in foreground to keep container alive
php-fpm -F -R -d "listen=127.0.0.1:9000"