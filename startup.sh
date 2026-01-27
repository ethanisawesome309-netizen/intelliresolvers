#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
echo "Starting Startup Script at $(date)"
# Force the Node path to ensure modules are found
export NODE_PATH=/home/site/wwwroot/node_modules

# --- 2. DEPENDENCY INSTALLATION ---
if ! command -v node &> /dev/null; then
    apt-get update && apt-get install -y curl
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server --no-install-recommends
    hash -r 
fi

# --- NEW INSTALLS FOR AI DOCUMENT READER ---
cd /home/site/wwwroot
# Added --prefer-offline to speed up restarts and --no-save to avoid lockfile churn
npm install pdf-extraction mammoth @google/generative-ai --save

# --- 3. SERVICE: REDIS ---
service redis-server start || redis-server --daemonize yes

# --- 4. NGINX CONFIGURATION ---
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    service nginx restart
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
cd /home/site/wwwroot
NODE_EXE=$(which node)
pkill -f socket-server.mjs || true

# Explicitly passing the absolute path to node_modules in the execution command
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