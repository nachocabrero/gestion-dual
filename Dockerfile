# syntax=docker/dockerfile:1

FROM php:8.1-fpm AS base

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user matching host uid/gid (1003:1004) so bind-mounts are writable
RUN groupadd --gid 1004 nacho2 && useradd -g 1004 -G www-data,root -m -s /bin/bash --uid 1003 laravel \
    && mkdir -p /app && chown -R 1003:1004 /app
USER laravel

WORKDIR /app

# Copy application files
COPY --chown=laravel:laravel . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]