all: fix-chmod install-deps install

fix-chmod:
	chmod -R 666 /etc/iqrf-daemon/
	chmod 777 /etc/iqrf-daemon/

install:
	cd /var/www/ && git clone https://github.com/iqrfsdk/iqrf-daemon-webapp
	# Download composer
	cd /var/www/iqrf-daemon-webapp/install/ && sh install-composer.sh
	# Download php dependencies
	cd /var/www/iqrf-daemon-webapp/ && php install/composer.phar install
	# Disable default virtualhost
	rm -f /etc/nginx/sites-enabled/default
	# Copy virtualhost
	cp nginx/iqrf-daemon-webapp.localhost /etc/nginx/sites-available/iqrf-daemon-webapp.localhost
	# Enable virtualhost
	ln -s /etc/nginx/sites-available/iqrf-daemon-webapp.localhost /etc/nginx/sites-enabled/iqrf-daemon-webapp.localhost
	# Fix PHP-FPM configuration
	sed 's/;cgi\.fix_pathinfo=1/cgi\.fix_pathinfo=0/g' /etc/php5/fpm/php.ini > /etc/php5/fpm/php.ini
	# Fix owner for directory
	chown -R www-data:www-data /var/www/iqrf-daemon-webapp/
	# Restart PHP-FPM
	service php5-fpm restart
	# Restart nginx
	service nginx restart

install-deps:
	apt-get update
	# Install nginx and php5
	apt-get install php5 php5-common php5-fpm php5-curl php5-json php5-sqlite nginx-full
