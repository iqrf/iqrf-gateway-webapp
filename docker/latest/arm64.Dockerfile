FROM arm64v8/composer:latest

MAINTAINER Roman Ondráček <roman.ondracek@iqrf.com>
LABEL maintainer="roman.ondracek@iqrf.com"

WORKDIR /var/www/iqrf-gateway-webapp

RUN git clone https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp.git .
RUN composer install
RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN chmod 777 log/ \
 && chmod 777 temp/
RUN bin/manager database:create \
 && bin/manager migrations:migrate --no-interaction

CMD [ "php", "-S", "[::]:8080", "-t", "/var/www/iqrf-gateway-webapp/www/" ]

EXPOSE 8080
