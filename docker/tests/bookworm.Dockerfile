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

FROM iqrftech/debian-base-builder:debian-bookworm-amd64

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y curl wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && curl -sL https://deb.nodesource.com/setup_lts.x | bash - \
 && apt-get update \
 && apt-get install --no-install-recommends -y composer debhelper dh-apache2 jq nodejs pkg-php-tools \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-pcov php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
    php7.4 php7.4-common php7.4-cgi php7.4-cli php7.4-curl php7.4-json php7.4-pcov php7.4-phpdbg php7.4-mbstring php7.4-sqlite3 php7.4-xml php7.4-zip \
    php8.0 php8.0-common php8.0-cgi php8.0-cli php8.0-curl php8.0-pcov php8.0-phpdbg php8.0-mbstring php8.0-sqlite3 php8.0-xml php8.0-zip \
    php8.1 php8.1-common php8.1-cgi php8.1-cli php8.1-curl php8.1-pcov php8.1-phpdbg php8.1-mbstring php8.1-sqlite3 php8.1-xml php8.1-zip \
	php8.2 php8.2-common php8.2-cgi php8.2-cli php8.2-curl php8.2-pcov php8.2-phpdbg php8.2-mbstring php8.2-sqlite3 php8.2-xml php8.2-zip \
    php php-common php-cgi php-cli php-curl php-json php-phpdbg php-pcov php-mbstring php-sqlite3 php-xml php-zip \
 && npm install -g pnpm npm@~8 \
 && npm install -g @sentry/cli --unsafe-perm \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN update-alternatives --set php /usr/bin/php8.2
RUN update-alternatives --set phpdbg /usr/bin/phpdbg8.2
