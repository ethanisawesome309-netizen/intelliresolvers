#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"
export NODE_PATH=/home/site/wwwroot/node_modules

# --- 2. DEPENDENCY INSTALLATION (Upgraded to Redis Stack) ---
if ! command -v redis-stack-server &> /dev/null; then
    apt-get update && apt-get install -y curl gpg lsb-release --no-install-recommends
    
    # Add official Redis Stack repository
    curl -fsSL https://packages.redis.io/gpg | gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | tee /etc/apt/sources.list.d/redis.list
    
    # Install Node.js 18
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    
    # Install Node and Redis Stack
    apt-get update && apt-get install -y nodejs redis-stack-server --no-install-recommends
    hash -r 
fi

# --- NEW INSTALLS FOR AI DOCUMENT READER ---
cd /home/site/wwwroot
npm install pdf-extraction mammoth @google/generative-ai --save

# --- 3. SERVICE: REDIS STACK ---
# Stop default redis if it's running to free up port 6379
service redis-server stop || true
# Start Redis Stack with Search and Vector modules
/opt/redis-stack/bin/redis-stack-server --daemonize yes

# --- 4. NGINX CONFIGURATION ---
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    service nginx restart
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
cd /home/site/wwwroot
NODE_EXE=$(which node)
pkill -f socket-server.mjs || true
nohup env NODE_PATH=/home/site/wwwroot/node_modules $NODE_EXE socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES ---
mkdir -p /home/site/wwwroot/uploads/tickets
mkdir -p /var/run/php
chown -R www-data:www-data /home/site/wwwroot /var/run/php
chmod -R 755 /home/site/wwwroot
chmod -R 777 /home/site/wwwroot/uploads

# --- 7. START PHP-FPM ---
pkill -f php-fpm || true

# FINAL CUSTOM PERMISSIONS & RELOAD
chown -R www-data:www-data /home/site/wwwroot && \
chmod -R 755 /home/site/wwwroot && \
cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default && \
nginx -t && \
service nginx reload

php-fpm -F -R -d "listen=127.0.0.1:9000"