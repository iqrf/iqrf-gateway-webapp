FROM arm32v7/composer:latest

LABEL maintainer="roman.ondracek@iqrf.com"

WORKDIR /var/www/iqrf-gateway-webapp

RUN composer create-project iqrf/iqrf-gateway-webapp .
RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN chmod 777 log/ \
 && chmod 777 temp/
RUN bin/manager database:create \
 && bin/manager migrations:migrate --no-interaction

CMD [ "php", "-S", "[::]:8080", "-t", "/var/www/iqrf-gateway-webapp/www/" ]

EXPOSE 8080
