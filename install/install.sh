#!/bin/bash

set -e

WEBSERVER_DIRECTORY=/var/www
DAEMON_DIRECTORY=/etc/iqrf-daemon
WEBAPP_DIRECTORY=${WEBSERVER_DIRECTORY}/iqrf-daemon-webapp
SUDOERS_FILE=/etc/sudoers

apt-get update
# Install sudo, nginx and php5
apt-get install -y sudo php5 php5-common php5-fpm php5-curl php5-json php5-sqlite nginx-full
# Fix permissions for r/w
chmod -R 666 /etc/iqrf-daemon/
chmod 777 /etc/iqrf-daemon/
if [ ! -d "${WEBAPP_DIRECTORY}" ]; then
	cd ${WEBSERVER_DIRECTORY}
	git clone https://github.com/iqrfsdk/iqrf-daemon-webapp
else
	cd ${WEBAPP_DIRECTORY}
	git pull origin
	rm -rf ${WEBAPP_DIRECTORY}/temp/cache
fi
# Download composer
cd ${WEBAPP_DIRECTORY}/install/ && bash install-composer.sh
# Download php dependencies
cd ${WEBAPP_DIRECTORY} && php install/composer.phar install
# Disable default virtualhost
if [ -f /etc/nginx/sites-enabled/default ]; then
	rm /etc/nginx/sites-enabled/default
fi
# Copy virtualhost
cp ${WEBAPP_DIRECTORY}/install/nginx/iqrf-daemon-webapp.localhost /etc/nginx/sites-available/iqrf-daemon-webapp.localhost
# Enable virtualhost
if [ ! -f /etc/nginx/sites-enabled/iqrf-daemon-webapp.localhost ]; then
	ln -s /etc/nginx/sites-available/iqrf-daemon-webapp.localhost /etc/nginx/sites-enabled/iqrf-daemon-webapp.localhost
fi
# Fix PHP-FPM configuration
sed 's/;cgi\.fix_pathinfo=1/cgi\.fix_pathinfo=0/g' /etc/php5/fpm/php.ini > /etc/php5/fpm/php.ini
# Fix owner for directory
chown -R www-data:www-data ${WEBAPP_DIRECTORY}
# Add user web-data to sudo
if ! grep -q "www-data   ALL=(ALL:ALL) ALL   NOPASSWD: ALLD" "${SUDOERS_FILE}"; then
	echo "www-data   ALL=(ALL:ALL) ALL   NOPASSWD: ALLD" >> "${SUDOERS_FILE}"
fi
# Restart sudo
service sudo restart
# Restart PHP-FPM
service php5-fpm restart
# Restart nginx
service nginx restart
