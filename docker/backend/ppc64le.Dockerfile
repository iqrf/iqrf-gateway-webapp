FROM iqrftech/iqrf-gateway-webapp:tests-buster as builder

LABEL maintainer="roman.ondracek@iqrf.com"

COPY . /var/www
WORKDIR /var/www

RUN composer install

FROM ppc64le/php:7.4-fpm

LABEL maintainer="roman.ondracek@iqrf.com"

ENV DEBIAN_FRONTEND noninteractive

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl xsl zip

COPY --from=builder /var/www /var/www
WORKDIR /var/www

RUN apt-get update \
 && apt-get install --no-install-recommends -y git iproute2 procps sudo \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
COPY install/config/sudoers/iqrf-gateway-webapp	/etc/sudoers.d/

RUN sed -i "s/\t\"commit\"\: .*/\t\"commit\"\: \"`git rev-parse --verify HEAD`\",/" version.json
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker-compose'/g" app/config/config.neon
RUN chown -R www-data:www-data app/ log/ temp/ vendor/

USER www-data:www-data
RUN bin/manager database:create \
 && bin/manager migrations:migrate --no-interaction
