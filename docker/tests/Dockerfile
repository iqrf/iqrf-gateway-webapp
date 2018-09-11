FROM debian:latest

MAINTAINER Roman Ondráček <roman.ondracek@iqrf.com>
LABEL maintainer="roman.ondracek@iqrf.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y apt-transport-https lsb-release ca-certificates curl git wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && apt-get update \
 && apt-get install --no-install-recommends -y build-essential composer debhelper devscripts fakeroot git-buildpackage pkg-php-tools \
    php7.0 php7.0-common php7.0-cgi php7.0-cli php7.0-curl php7.0-json php7.0-phpdbg php7.0-mbstring php7.0-sqlite3 php7.0-xml php7.0-zip \
    php7.1 php7.1-common php7.1-cgi php7.1-cli php7.1-curl php7.1-json php7.1-phpdbg php7.1-mbstring php7.1-sqlite3 php7.1-xml php7.1-zip \
    php7.2 php7.2-common php7.2-cgi php7.2-cli php7.2-curl php7.2-json php7.2-phpdbg php7.2-mbstring php7.2-sqlite3 php7.2-xml php7.2-zip \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
	openssh-client python3-recommonmark python3-sphinx rsync \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
