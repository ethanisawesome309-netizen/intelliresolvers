#!/bin/bash

# --- 1. ENVIRONMENT SETUP ---
set -e # Exit on error (optional, but good for critical failures)
echo "Starting Startup Script at $(date)"

# --- 2. DEPENDENCY INSTALLATION (with Retries) ---
# Azure containers sometimes have locked apt processes at boot
install_packages() {
    echo "Updating and installing dependencies..."
    apt-get update || true
    apt-get install -y curl redis-server nodejs npm --no-install-recommends || {
        echo "Apt failed, retrying in 5s..."
        sleep 5
        apt-get install -f -y
    }
}

# Install Node 18 specifically if not present
if ! command -v node &> /dev/null; then
    echo "Node.js not found. Installing Node 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

install_packages

# --- 3. SERVICE: REDIS ---
echo "Starting Redis..."
mkdir -p /var/run/redis
redis-server --daemonize yes

# Wait for Redis to be ready so Node doesn't crash
MAX_RETRIES=5
COUNT=0
while ! redis-cli ping | grep -q PONG; do
    echo "Waiting for Redis... ($COUNT/$MAX_RETRIES)"
    sleep 2
    ((COUNT++))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "Redis failed to start. Proceeding anyway..."
        break
    fi
done

# --- 4. NGINX CONFIGURATION ---
echo "Syncing Nginx configurations..."
if [ -f /home/site/wwwroot/default.txt ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    # Ensure the enabled link exists or is the file itself
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
    
    # Test config before reloading
    if nginx -t; then
        service nginx reload
    else
        echo "Nginx config test failed! Keeping old config."
    fi
else
    echo "ERROR: /home/site/wwwroot/default.txt not found!"
fi

# --- 5. SERVICE: NODE.JS BRIDGE ---
echo "Preparing Node.js bridge..."
cd /home/site/wwwroot
if [ -f "package.json" ]; then
    npm install --production
fi

# Kill any existing node processes to prevent port conflicts on restart
pkill node || true

export PORT=3001
echo "Launching Node.js..."
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# --- 6. PERMISSIONS & DIRECTORIES ---
echo "Setting permissions..."
mkdir -p /var/run/php
chown -R www-data:www-data /var/run/php /home/site/wwwroot
chmod -R 755 /home/site/wwwroot

# --- 7. FINAL HANDOFF: PHP-FPM ---
# Using -F keeps it in foreground to keep container alive
echo "🚀 All systems go. Starting PHP-FPM..."
exec php-fpm