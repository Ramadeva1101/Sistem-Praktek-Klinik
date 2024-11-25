#!/bin/bash

# Install PHP dan dependencies yang dibutuhkan
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
apt-get update && apt-get install -y php8.1-cli php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath

# Download dan install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Proses instalasi dan build project
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
