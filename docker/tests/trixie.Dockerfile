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

FROM iqrftech/debian-base-builder:debian-trixie-amd64

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y curl wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && curl -sL https://deb.nodesource.com/setup_lts.x | bash - \
 && apt-get update \
 && apt-get install --no-install-recommends -y composer debhelper dh-apache2 jq nodejs pkg-php-tools \
    php8.1 php8.1-common php8.1-cgi php8.1-cli php8.1-curl php8.1-intl php8.1-pcov php8.1-phpdbg php8.1-mbstring php8.1-mysql php8.1-sqlite3 php8.1-xml php8.1-zip \
    php8.2 php8.2-common php8.2-cgi php8.2-cli php8.2-curl php8.2-intl php8.2-pcov php8.2-phpdbg php8.2-mbstring php8.2-mysql php8.2-sqlite3 php8.2-xml php8.2-zip \
    php8.3 php8.3-common php8.3-cgi php8.3-cli php8.3-curl php8.3-intl php8.3-pcov php8.3-phpdbg php8.3-mbstring php8.3-mysql php8.3-sqlite3 php8.3-xml php8.3-zip \
    php8.4 php8.4-common php8.4-cgi php8.4-cli php8.4-curl php8.4-intl php8.4-pcov php8.4-phpdbg php8.4-mbstring php8.4-mysql php8.4-sqlite3 php8.4-xml php8.4-zip \
	php8.5 php8.5-common php8.5-cgi php8.5-cli php8.5-curl php8.5-intl php8.5-phpdbg php8.5-mbstring php8.5-mysql php8.5-sqlite3 php8.5-xml php8.5-zip \
    php php-common php-cgi php-cli php-curl php-json php-intl php-phpdbg php-pcov php-mbstring php-mysql php-sqlite3 php-xml php-zip \
 && npm install -g pnpm npm \
 && npm install -g @sentry/cli --unsafe-perm \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN update-alternatives --set php /usr/bin/php8.3
RUN update-alternatives --set phpdbg /usr/bin/phpdbg8.3
