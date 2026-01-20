#!/bin/bash

# 1. Immediate Nginx & PHP setup (Get the site online first)
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
service nginx restart
php-fpm -D

# 2. Wait for background apt locks to clear
echo "Waiting for system locks to release..."
while fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1; do sleep 5; done

# 3. Enforce Node 18 & Redis Installation
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs redis-server

# 4. Global tools and Redis startup
npm install pm2 -g
redis-server --daemonize yes

# 5. Application Launch
cd /home/site/wwwroot
npm install # Ensure socket.io and ioredis are present
pm2 delete all || true
pm2 start socket-server.mjs --name "socket-bridge"

echo "🚀 Startup Complete. Node $(node -v) is running."