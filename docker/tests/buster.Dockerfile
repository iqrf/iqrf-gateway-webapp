FROM debian:buster

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y apt-transport-https build-essential ca-certificates composer curl debhelper devscripts \
    graphviz fakeroot git git-buildpackage lsb-release openssh-client python3-recommonmark python3-sphinx pkg-php-tools rsync unzip wget zip \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
    php php-common php-cgi php-cli php-curl php-json php-phpdbg php-mbstring php-sqlite3 php-xml php-zip python3-pip dh-apache2 \
 && pip3 install setuptools \
 && pip3 install sphinx-corlab-theme \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
