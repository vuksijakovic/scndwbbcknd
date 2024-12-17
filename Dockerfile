FROM php:8.2-apache

# Instalacija potrebnih ekstenzija
RUN apt-get update && apt-get install -y \
    libzip-dev unzip libxml2-dev \
    && docker-php-ext-install zip mysqli pdo pdo_mysql \
    && apt-get clean
RUN a2enmod headers

# Instalacija Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/

# Postavljanje permisija
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Aktivacija mod_rewrite i Apache konfiguracije
RUN a2enmod rewrite 

COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Eksport porta 80
EXPOSE 80

# Pokretanje Apache servera
CMD ["apache2-foreground"]
