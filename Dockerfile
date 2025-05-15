FROM php:8.2-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
\
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev \
        libonig-dev libxslt-dev unzip librabbitmq-dev libssh-dev \
        default-mysql-client \
\
    && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen \
\
    && curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer \
\
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv $(find / -name symfony) /usr/local/bin \
\
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl \
\
    && pecl install apcu && docker-php-ext-enable apcu \
    && pecl install amqp && docker-php-ext-enable amqp \
\
    && a2enmod rewrite \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/app/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www
