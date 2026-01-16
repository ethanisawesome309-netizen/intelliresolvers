#!/bin/bash

# 1. Update and install dependencies only if they are missing
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs redis-server
fi

# 2. Start services
service redis-server start
service php8.2-fpm start # Ensure PHP is running

# 3. Apply your Nginx config
if [ -f "/home/site/wwwroot/default.txt" ]; then
    cp /home/site/wwwroot/default.txt /etc/nginx/sites-available/default
    service nginx reload
else
    echo "Nginx config file not found!"
fi

# 4. Start the socket server in the background
cd /home/site/wwwroot
node socket-server.mjs &

# 5. Keep the container alive (Required for Azure)
wait