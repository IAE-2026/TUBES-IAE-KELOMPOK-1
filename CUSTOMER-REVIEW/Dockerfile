FROM php:8.2-apache

# Use mlocati/php-extension-installer which is much more stable for Docker
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y git curl zip unzip && \
    install-php-extensions pdo pdo_mysql mbstring

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# In case the directory is empty or vendor not present, we should allow composer to run, 
# but if it fails we don't crash the container completely if it's just a warning.
RUN composer install --no-dev --optimize-autoloader || true

RUN chown -R www-data:www-data storage bootstrap/cache || true

EXPOSE 80