FROM php:8.4-fpm

# Instal dependencies sistem dan driver PostgreSQL
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Bersihkan cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP (Wajib ada pdo_pgsql)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd opcache

# Ambil Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy semua file proyek
COPY . .

# Beri izin akses ke folder storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan Nginx dan PHP-FPM
EXPOSE 8000
CMD service nginx start && php-fpm