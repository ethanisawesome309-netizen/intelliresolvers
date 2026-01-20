#!/bin/bash

# 1. KILL EXISTING PROCESSES (Fixes "Address already in use")
echo "Cleaning up existing services..."
fuser -k 9000/tcp || true   # Kill whatever is on PHP's port
fuser -k 80/tcp || true     # Kill whatever is on Nginx port
fuser -k 8080/tcp || true   # Kill whatever is on Azure's default port

# 2. CONFIGURE NGINX
echo "Configuring Nginx..."
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
# Check if config is valid before restarting
nginx -t && service nginx restart

# 3. INSTALL NODE 18 & REDIS
# Added 'NODESOURCE_NOCONFIRM' to skip the 10-second wait
echo "Installing Node 18 and Redis..."
export NODESOURCE_NOCONFIRM=true
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs redis-server

# 4. START REDIS
redis-server --daemonize yes

# 5. START PHP
echo "Starting PHP..."
# Use -D to run in background, but ensure no other FPM is running
service php8.2-fpm stop || true
php-fpm -D

# 6. START NODE SOCKET SERVER
echo "Starting Node Server..."
cd /home/site/wwwroot
# Kill old node process if it exists
pkill node || true
nohup node socket-server.mjs > node_logs.txt 2>&1 &

echo "🚀 All systems go!"