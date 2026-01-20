#!/bin/bash

# 1. Update and Install Node.js + Redis
# We use -y to skip prompts and ensure the script doesn't hang
echo "Checking dependencies..."
apt-get update
apt-get install -y curl redis-server

# Install Node.js 18.x
if ! command -v node &> /dev/null; then
    echo "Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# 2. Start Redis
echo "Starting Redis..."
redis-server --daemonize yes

# 3. Setup PHP Socket Directory
mkdir -p /var/run/php
chown -R www-data:www-data /var/run/php

# 4. Sync Nginx Config (Both locations)
echo "Applying Nginx Config..."
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
cp /home/site/wwwroot/default.txt /etc/nginx/sites-enabled/default
service nginx reload

# 5. Start Node.js Bridge
echo "Starting Node.js Bridge..."
cd /home/site/wwwroot
# Install socket.io and redis npm packages if they aren't there
npm install
export PORT=3001
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 6. Final Permissions and Start PHP
echo "Handing over to PHP-FPM..."
chown -R www-data:www-data /home/site/wwwroot
php-fpm -F