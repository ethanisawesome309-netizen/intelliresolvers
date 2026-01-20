#!/bin/bash

# 1. Start Redis
redis-server --daemonize yes

# 2. Sync Nginx config
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default

# 3. Start Node.js bridge
cd /home/site/wwwroot
export PORT=3001
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 4. Fix permissions for the Socket and Web files
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot

# 5. Reload Nginx to apply default.txt changes
service nginx reload

# 6. Start PHP-FPM in foreground (Keeps the container running)
echo "Starting PHP-FPM..."
php-fpm -F