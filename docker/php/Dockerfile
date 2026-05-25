# 1. La Imagen Base (El Motor)
FROM php:8.2-fpm

# 2. El Directorio de Trabajo (La Ubicación del Monorepo)
WORKDIR /var/www/html

# 3. Dependencias del Sistema y Extensiones estrictas de PHP/MySQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip

# 4. Inyección de Composer (Gestor de Paquetes)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Permisos de Seguridad del Sistema Operativo
RUN usermod -u 1000 www-data \
    && chown -R www-data:www-data /var/www/html