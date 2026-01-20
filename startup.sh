#!/bin/bash

# 1. Install Node.js (since it's missing from the image)
echo "Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs

# 2. Start Redis
echo "Starting Redis..."
redis-server --daemonize yes

# 3. Apply Nginx Config
echo "Configuring Nginx..."
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
service nginx reload

# 4. Start Node.js Bridge
echo "Starting Node Server..."
cd /home/site/wwwroot
npm install  # Ensures socket.io and redis packages exist
export PORT=3001
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 5. Fix Permissions
chown -R www-data:www-data /home/site/wwwroot
chmod -R 755 /home/site/wwwroot

# 6. Start PHP-FPM in foreground
echo "Handing over to PHP-FPM..."
php-fpm -F