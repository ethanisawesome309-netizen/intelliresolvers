#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCIES ---
if ! command -v node &> /dev/null; then
    apt-get update && apt-get install -y curl
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

# --- 3. SERVICE: REDIS ---
service redis-server start || redis-server --daemonize yes

# --- 4. PREPARE APP ---
cd /home/site/wwwroot
NODE_EXE=$(which node)
if [ ! -d "node_modules" ]; then
    $NODE_EXE /usr/bin/npm install --production
fi

pkill -f socket-server.mjs || true
export PORT=3001 
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 5. DIRECTORY & PERMISSIONS SETUP ---
echo "Finalizing folders and permissions..."
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php
mkdir -p /var/log/php-fpm

# ✅ FIX: Apply recursive permissions so PHP can "see" into subfolders
chown -R www-data:www-data /home/site/wwwroot /var/run/php /var/log/php-fpm
chmod -R 755 /home/site/wwwroot
chmod -R 777 /home/site/wwwroot/uploads

# --- 6. NGINX SYNC ---
rm -rf /etc/nginx/sites-enabled/*
rm -rf /etc/nginx/sites-available/default

if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    nginx -t && service nginx restart
fi

# --- 7. START PHP ---
echo "🚀 Starting PHP-FPM..."
pkill -f php-fpm || true

# ✅ FIX: Force PHP-FPM to listen on 127.0.0.1:9000 specifically
php-fpm -F -R -d "listen=127.0.0.1:9000"