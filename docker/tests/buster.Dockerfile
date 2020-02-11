FROM debian:buster

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y lsb-release ca-certificates curl git wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && apt-get update \
 && apt-get install --no-install-recommends -y build-essential composer debhelper devscripts \
    dh-apache2 graphviz fakeroot git-buildpackage openssh-client pkg-php-tools rsync \
    php7.2 php7.2-common php7.2-cgi php7.2-cli php7.2-curl php7.2-json php7.2-phpdbg php7.2-mbstring php7.2-sqlite3 php7.2-xml php7.2-zip \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
    php7.4 php7.4-common php7.4-cgi php7.4-cli php7.4-curl php7.4-json php7.4-phpdbg php7.4-mbstring php7.4-sqlite3 php7.4-xml php7.4-zip \
    php php-common php-cgi php-cli php-curl php-json php-phpdbg php-mbstring php-sqlite3 php-xml php-zip \
    python3-pip python3-recommonmark python3-sphinx python3-setuptools python3-wheel \
 && pip3 install sphinx-corlab-theme \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
