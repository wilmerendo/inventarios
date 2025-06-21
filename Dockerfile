# Usar imagen oficial de PHP con Apache para Windows
FROM php:8.1-apache

# Instalar dependencias para Windows
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configuraciones específicas para Windows
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos (usar rutas de Windows)
COPY ./inventario /var/www/html/

# Permisos para Windows
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Instalar TCPDF
WORKDIR /var/www/html
RUN composer require tecnickcom/tcpdf

EXPOSE 80