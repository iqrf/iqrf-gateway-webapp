FROM debian:buster

MAINTAINER Roman Ondráček <roman.ondracek@iqrf.com>
LABEL maintainer="roman.ondracek@iqrf.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y apt-transport-https build-essential ca-certificates composer curl debhelper devscripts \
    fakeroot git git-buildpackage lsb-release openssh-client python3-recommonmark python3-sphinx pkg-php-tools rsync unzip wget zip \
    php7.2 php7.2-common php7.2-cgi php7.2-cli php7.2-curl php7.2-json php7.2-phpdbg php7.2-mbstring php7.2-sqlite3 php7.2-xml php7.2-zip \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
