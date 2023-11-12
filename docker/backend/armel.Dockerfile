# Copyright 2017-2023 IQRF Tech s.r.o.
# Copyright 2019-2023 MICRORISC s.r.o.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

FROM iqrftech/iqrf-gateway-webapp:tests-bullseye AS builder

LABEL maintainer="roman.ondracek@iqrf.com"

COPY . /var/www
WORKDIR /var/www

RUN composer install --ignore-platform-req=ext-intl

FROM arm32v5/php:8.2-fpm

LABEL maintainer="roman.ondracek@iqrf.com"

ENV DEBIAN_FRONTEND noninteractive

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl sockets xsl zip

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

COPY docker/backend/run.sh run.sh
CMD ["bash", "run.sh"]
