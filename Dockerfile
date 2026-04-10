# --- ETAPA BASE ---
FROM php:8.2-fpm AS base
WORKDIR /var/www
RUN apt-get update && apt-get install -y libpq-dev zip unzip git \
    && docker-php-ext-install pdo_pgsql bcmath

# --- ETAPA DE DESARROLLO ---
FROM base AS development
# No copiamos el código, se usará un volumen en el compose
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
CMD ["php-fpm"]

# --- ETAPA DE PRODUCCIÓN ---
FROM base AS production
# Copiar el código del proyecto
COPY . .
# Instalar dependencias sin las de desarrollo
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader
# Permisos seguros para Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
USER www-data
CMD ["php-fpm"]
