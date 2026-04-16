FROM php:8.3-fpm

# System deps
RUN apt-get update && apt-get install -y \
  git curl unzip zip ca-certificates gnupg \
  libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
  libzip-dev libicu-dev libonig-dev \
  mariadb-client \
  openssh-client openssh-server \
  sudo tini \
  && rm -rf /var/lib/apt/lists/*

# PHP extensions commonly used by Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring zip exif pcntl intl gd opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js (optional for front-end tools)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update && apt-get install -y nodejs \
  && rm -rf /var/lib/apt/lists/*

# Tailscale (inside container)
RUN curl -fsSL https://pkgs.tailscale.com/stable/debian/bookworm.noarmor.gpg \
  | tee /usr/share/keyrings/tailscale-archive-keyring.gpg >/dev/null \
  && curl -fsSL https://pkgs.tailscale.com/stable/debian/bookworm.tailscale-keyring.list \
  | tee /etc/apt/sources.list.d/tailscale.list >/dev/null \
  && apt-get update && apt-get install -y tailscale \
  && rm -rf /var/lib/apt/lists/*

# Prepare SSH server (optional)
RUN mkdir -p /var/run/sshd \
  && echo 'root:root' | chpasswd \
  && sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config \
  && sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config

WORKDIR /var/www/html

# Copy entrypoint and make executable
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Use tini as entrypoint to ensure proper signal forwarding
ENTRYPOINT ["tini", "--", "/entrypoint.sh"]
EXPOSE 9000