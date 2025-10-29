#!/bin/bash

# Wait for database to be ready (optional, but good practice)
echo "Waiting for database connection..."
timeout=60
while [ $timeout -gt 0 ]; do
    if php artisan migrate:status > /dev/null 2>&1; then
        echo "Database is ready!"
        break
    fi
    echo "Waiting for database... ($timeout seconds remaining)"
    sleep 1
    timeout=$((timeout - 1))
done

if [ $timeout -eq 0 ]; then
    echo "Database connection timeout. Starting without migrations."
else
    # Run database migrations
    echo "Running migrations..."
    php artisan migrate --force
fi

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Fix Swagger UI initializer to point to our API instead of Petstore
echo "Fixing Swagger UI configuration..."
sed -i 's|https://petstore.swagger.io/v2/swagger.json|/api-docs.json|g' public/docs/swagger-initializer.js

# Start Apache
echo "Starting Apache..."
apache2-foreground