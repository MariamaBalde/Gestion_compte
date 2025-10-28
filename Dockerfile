# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies
RUN composer update --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Generate application key
RUN php artisan key:generate --show | grep -o 'base64:[^"]*' | cut -d':' -f2 > /tmp/app_key.txt

# Generate Swagger documentation
RUN php artisan l5-swagger:generate

# Copy Swagger docs to public directory for production
RUN cp storage/api-docs/api-docs.json public/api-docs.json
RUN cp -r vendor/swagger-api/swagger-ui/dist/* public/docs/

# Create .env file with production settings
RUN echo "APP_NAME=Laravel" > /var/www/html/.env && \
    echo "APP_ENV=production" >> /var/www/html/.env && \
    echo "APP_KEY=base64:$(cat /tmp/app_key.txt)" >> /var/www/html/.env && \
    echo "APP_DEBUG=true" >> /var/www/html/.env && \
    echo "APP_URL=https://gestion-compte-1izl.onrender.com" >> /var/www/html/.env && \
    echo "L5_SWAGGER_CONST_HOST=https://gestion-compte-1izl.onrender.com" >> /var/www/html/.env && \
    echo "LOG_CHANNEL=stack" >> /var/www/html/.env && \
    echo "DB_CONNECTION=pgsql" >> /var/www/html/.env && \
    echo "DB_HOST=\${PGHOST}" >> /var/www/html/.env && \
    echo "DB_PORT=\${PGPORT}" >> /var/www/html/.env && \
    echo "DB_DATABASE=\${PGDATABASE}" >> /var/www/html/.env && \
    echo "DB_USERNAME=\${PGUSER}" >> /var/www/html/.env && \
    echo "DB_PASSWORD=\${PGPASSWORD}" >> /var/www/html/.env && \
    echo "CACHE_DRIVER=file" >> /var/www/html/.env && \
    echo "QUEUE_CONNECTION=sync" >> /var/www/html/.env && \
    echo "SESSION_DRIVER=file" >> /var/www/html/.env && \
    echo "REDIS_HOST=\${REDIS_HOST}" >> /var/www/html/.env && \
    echo "REDIS_PORT=\${REDIS_PORT}" >> /var/www/html/.env && \
    echo "REDIS_PASSWORD=\${REDIS_PASSWORD}" >> /var/www/html/.env

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache to serve from /var/www/html/public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Copy start script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Start the application
CMD ["/usr/local/bin/start.sh"]