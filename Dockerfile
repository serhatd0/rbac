FROM php:8.2-fpm-alpine

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy rest of the app
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www/html
