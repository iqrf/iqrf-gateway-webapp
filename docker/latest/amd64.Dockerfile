FROM debian:latest

MAINTAINER Roman Ondráček <roman.ondracek@iqrf.com>
LABEL maintainer="roman.ondracek@iqrf.com"

RUN apt-get update \
 && apt-get install --no-install-recommends -y apt-transport-https lsb-release ca-certificates curl git wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && apt-get update \
 && apt-get install --no-install-recommends -y composer git \
    php7.2 php7.2-common php7.2-cgi php7.2-cli php7.2-curl php7.2-fpm php7.2-json php7.2-mbstring php7.2-sqlite3 php7.2-xml php7.2-zip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/iqrf-gateway-webapp

RUN git clone https://github.com/iqrfsdk/iqrf-gateway-webapp .
RUN composer install
RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN chmod 777 log/ \
 && chmod 777 temp/

CMD [ "php", "-S", "[::]:8080", "-t", "/var/www/iqrf-gateway-webapp/www/" ]

EXPOSE 8080
