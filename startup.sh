#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script..."

# --- 2. DEPENDENCIES ---
if ! command -v node &> /dev/null; then
    apt-get update && apt-get install -y curl
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

# --- 3. SERVICES ---
service redis-server start || redis-server --daemonize yes

# --- 4. PREPARE APP ---
cd /home/site/wwwroot
NODE_EXE=$(which node)
if [ ! -d "node_modules" ]; then
    $NODE_EXE /usr/bin/npm install --production
fi

pkill -f socket-server.mjs || true
nohup $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 5. PERMISSIONS & DIRECTORY SETUP ---
echo "Fixing permissions and folders..."
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php
mkdir -p /var/log/php-fpm

# Apply ownership
chown -R www-data:www-data /home/site/wwwroot /var/run/php /var/log/php-fpm
chmod -R 755 /home/site/wwwroot

# --- 6. NGINX SYNC (The 404 Killer) ---
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    # ✅ FORCE RESTART: Reload doesn't always clear 404 errors in Azure
    service nginx restart
fi

# --- 7. START PHP ---
echo "🚀 Starting PHP-FPM..."
pkill -f php-fpm || true
php-fpm -F -R -d "listen=127.0.0.1:9000"