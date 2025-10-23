#!/bin/bash

# Run database migrations
php artisan migrate --force

# Start Apache
apache2-foreground