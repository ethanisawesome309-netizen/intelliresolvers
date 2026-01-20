#!/bin/bash

# 1. Install Node.js
echo "Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs

# 2. Start Redis
echo "Starting Redis..."
redis-server --daemonize yes

# 3. Apply Nginx Config (Overwriting BOTH locations to be sure)
echo "Configuring Nginx..."
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
cp /home/site/wwwroot/default.txt /etc/nginx/sites-enabled/default

# 4. Start Node.js Bridge
echo "Starting Node Server..."
cd /home/site/wwwroot
npm install
export PORT=3001
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 5. Fix Permissions & Socket Directory
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot

# 6. Final Reload and Start PHP
service nginx reload
echo "🚀 Handing over to PHP-FPM..."
php-fpm -F