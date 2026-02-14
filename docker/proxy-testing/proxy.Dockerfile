FROM php:8.2-fpm

RUN apt-get update \
 && apt-get install -y git unzip libicu-dev libxslt1-dev libzip-dev \
 && docker-php-ext-install intl sockets xsl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /app
COPY docker/proxy-testing/proxy-config.neon /app/app/config/proxy-config.neon
WORKDIR /app

RUN composer install --no-interaction --optimize-autoloader

RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN chmod 777 log/ \
 && chmod 777 temp/
RUN rm -rf app/config/database.db \
 && bin/manager migrations:migrate --no-interaction \
 && bin/manager user:add -u admin -p '9vG$kdP&!zX@rL#Y' -r admin -l en

EXPOSE 8080 9000
