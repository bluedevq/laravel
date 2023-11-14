#!/bin/sh
# Install composer
composer install

# Make .env file
cp .env.example .env

# Migrating db, generate key
php artisan key:generate
php artisan optimize:clear
php artisan storage:link
php artisan migrate
