#!/bin/bash

sudo -u www-data bin/manager database:create
sudo -u www-data bin/manager migrations:migrate --no-interaction

php-fpm
