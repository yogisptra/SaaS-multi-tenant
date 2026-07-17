FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libsqlite3-dev

RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev

RUN cp .env.example .env
RUN php artisan key:generate
RUN php artisan jwt:secret
RUN touch database/database.sqlite
RUN php artisan migrate:fresh --seed --force

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
