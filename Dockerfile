FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    gnupg2 \
    ca-certificates \
    apt-transport-https \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip gd

# Microsoft ODBC driver 18 (required for sqlsrv / pdo_sqlsrv)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor > /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] \
        https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (layer-cache friendly)
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Copy application source
COPY . .

# Ensure Laravel writable directories exist with correct ownership
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
