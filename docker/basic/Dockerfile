FROM php:7.1

LABEL maintainer="roman.ondracek@iqrf.com"

RUN apt-get update && apt-get install -y git wget

WORKDIR /var/www/html

RUN git clone https://github.com/iqrfsdk/iqrf-daemon-webapp .
RUN bash install/install-composer.sh
RUN php composer.phar install
RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon

CMD [ "php", "-S", "[::]:8080", "-t", "/var/www/html/www/" ]

EXPOSE 8080
