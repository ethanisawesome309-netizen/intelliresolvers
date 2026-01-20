# Start Redis
redis-server --daemonize yes

# Copy Nginx config
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default

# Start Node bridge
cd /home/site/wwwroot
export PORT=3001
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# Fix permissions (Crucial for 502 errors)
chown -R www-data:www-data /home/site/wwwroot
chmod -R 755 /home/site/wwwroot

# Reload Nginx to pick up the new default.txt
service nginx reload

# Start PHP-FPM
php-fpm -F