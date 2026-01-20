#!/bin/bash

# 1. Start Redis in the background
redis-server --daemonize yes

# 2. Fix Nginx config and reload
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
service nginx reload

# 3. Clean up and Start Node.js
# Note: Using port 3001 internally as defined in your .mjs
cd /home/site/wwwroot
pkill node || true
export PORT=3001 
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 4. Set permissions so Nginx can read files
chown -R www-data:www-data /home/site/wwwroot
chmod -R 755 /home/site/wwwroot

# 5. Start PHP-FPM in foreground (Required to keep container alive)
echo "🚀 Services started. Handing over to PHP-FPM..."
php-fpm -F