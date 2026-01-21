#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script..."

# --- 2. DEPENDENCY INSTALLATION ---
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

# --- 3. SERVICE: REDIS ---
redis-server --daemonize yes

# --- 4. NGINX CONFIGURATION ---
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    service nginx reload
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
cd /home/site/wwwroot
nohup node socket-server.mjs > /home/site/wwwroot/node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES (NEEDED FOR UPLOAD) ---
# ✅ Added for file upload feature
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php

# Ensure permissions are correct for PHP to save files
chown -R www-data:www-data /home/site/wwwroot
chmod -R 755 /home/site/wwwroot
chmod -R 777 /home/site/wwwroot/uploads

# --- 7. START PHP-FPM (YOUR WORKING COMMAND) ---
echo "🚀 Starting PHP-FPM..."
pkill -f php-fpm || true
# ✅ This matches your successful manual test
php-fpm -F -R -d "listen=127.0.0.1:9000"