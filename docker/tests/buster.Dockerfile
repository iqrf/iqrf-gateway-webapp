FROM iqrftech/debian-base-builder:debian-buster-amd64

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
 && apt-get install --no-install-recommends -y curl wget zip unzip \
 && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
 && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
 && curl -sL https://deb.nodesource.com/setup_lts.x | bash - \
 && apt-get update \
 && apt-get install --no-install-recommends -y composer debhelper dh-apache2 graphviz nodejs pkg-php-tools \
    php7.3 php7.3-common php7.3-cgi php7.3-cli php7.3-curl php7.3-json php7.3-phpdbg php7.3-mbstring php7.3-sqlite3 php7.3-xml php7.3-zip \
    php7.4 php7.4-common php7.4-cgi php7.4-cli php7.4-curl php7.4-json php7.4-phpdbg php7.4-mbstring php7.4-sqlite3 php7.4-xml php7.4-zip \
    php php-common php-cgi php-cli php-curl php-json php-phpdbg php-pcov php-mbstring php-sqlite3 php-xml php-zip \
 && pip3 install sphinx-corlab-theme \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
