FROM composer:latest

LABEL maintainer="roman.ondracek@iqrf.com"

COPY ./ /app
WORKDIR /app
RUN composer install
RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN sed -i "s~http://localhost:8080/~http://172.16.10.2:8080/~g" tests/features/bootstrap/BaseContext.php
RUN chmod 777 log/ \
 && chmod 777 temp/
RUN rm -rf app/config/database.db \
 && bin/manager database:create \
 && bin/manager migrations:migrate --no-interaction \
 && bin/manager user:add -u admin -p iqrf -r power -l en

CMD [ "php", "-S", "[::]:8080", "-t", "/app/www/" ]

EXPOSE 8080
