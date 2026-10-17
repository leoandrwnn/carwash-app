FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite headers

COPY zz-apache-security.conf /etc/apache2/conf-available/zz-apache-security.conf
RUN a2enconf zz-apache-security

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
