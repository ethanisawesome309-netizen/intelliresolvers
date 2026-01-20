#!/bin/bash

# 1. Install Dependencies - Ensure this blocks the script until done
echo "Installing Node and Redis..."
apt-get update
# We don't use & here; we want the script to wait.
apt-get install -y redis-server curl

# Install Node 18
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# 2. Verify Node is actually here now
if ! command -v node &> /dev/null; then
    echo "ERROR: Node installation failed!"
    exit 1
fi

# 3. Start Redis and Wait for it
redis-server --daemonize yes
until redis-cli ping | grep -q PONG; do sleep 1; done

# 4. Final Prep and Start Node
cd /home/site/wwwroot
npm install
nohup node socket-server.mjs > node_logs.txt 2>&1 &

# 5. Start PHP
php-fpm -F