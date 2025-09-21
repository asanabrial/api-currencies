FROM php:8.4-fpm

WORKDIR /var/www

# Install essentials and tools
RUN apt-get update && apt-get install -y \
    libpq-dev postgresql-client git unzip \
    && docker-php-ext-install pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock /var/www/
RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist

# Copy application code
COPY --chown=www-data:www-data . /var/www
RUN composer dump-autoload --optimize && \
    chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data
EXPOSE 9000
CMD ["/usr/local/bin/entrypoint.sh"]