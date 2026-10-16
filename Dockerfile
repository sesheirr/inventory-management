FROM php:8.3-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and build libraries for PHP extensions
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libstdc++ \
    libgcc \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    icu-libs \
    sqlite-libs \
    mariadb-connector-c \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    sqlite-dev \
    mariadb-connector-c-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        gd \
        zip \
        intl \
        bcmath \
        pcntl \
        exif \
    && apk del \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        icu-dev \
        sqlite-dev \
        mariadb-connector-c-dev \
        linux-headers

# Copy Composer from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy Bun & bunx from official bun alpine image
COPY --from=oven/bun:alpine /usr/local/bin/bun /usr/local/bin/bun
RUN ln -s /usr/local/bin/bun /usr/local/bin/bunx

# Copy runner script
COPY docker/run-dev.sh /usr/local/bin/run-dev.sh
RUN chmod +x /usr/local/bin/run-dev.sh

# Expose ports:
# 8000: Laravel HTTP Server
# 5173: Vite Dev Server HMR
EXPOSE 8000 5173

CMD ["/usr/local/bin/run-dev.sh"]
