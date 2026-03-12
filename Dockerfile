# Gamitin ang official PHP-FPM image
FROM php:8.2-fpm

# I-install ang system dependencies at PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# I-clear ang cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# I-install ang PHP extensions (mysql o pgsql depende sa DB mo)
RUN docker-php-ext-install ppa-bcmath gd mbstring intl
RUN docker-php-ext-install pmd_mysql mbstring exif pcntl bcmath gd

# Para sa PostgreSQL (kung Render/Vercel Postgres gamit mo)
RUN docker-php-ext-install pdo pdo_pgsql

# I-install ang Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# I-set ang working directory
WORKDIR /var/www

# I-copy ang files ng system mo
COPY . .

# I-install ang dependencies ng Laravel
RUN composer install --no-dev --optimize-autoloader

# I-set ang permissions para sa storage at cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# I-expose ang port (default sa Render ay 80 o 10000)
EXPOSE 80

# Start command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]