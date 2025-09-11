# Copyright 2017-2025 IQRF Tech s.r.o.
# Copyright 2019-2025 MICRORISC s.r.o.
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

FROM composer:latest as builder

LABEL maintainer="roman.ondracek@iqrf.com"

COPY ./ /app
WORKDIR /app
RUN composer install --ignore-platform-req=ext-intl

FROM amd64/php:8.2

LABEL maintainer="roman.ondracek@iqrf.com"

ENV DEBIAN_FRONTEND noninteractive

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl sockets xsl zip

COPY --from=builder /app /app
WORKDIR /app

RUN sed -i 's/sudo\:\ true/sudo\:\ false/g' app/config/config.neon
RUN sed -i "s/initDaemon: 'systemd'/initDaemon: 'docker'/g" app/config/config.neon
RUN chmod 777 log/ \
 && chmod 777 temp/
RUN rm -rf app/config/database.db \
 && bin/manager migrations:migrate --no-interaction \
 && bin/manager user:add -u admin -p '9vG$kdP&!zX@rL#Y' -r admin -l en

CMD [ "php", "-S", "[::]:8080", "-t", "/app/www/" ]

EXPOSE 8080
