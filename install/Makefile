all: install-deps install

install:
	cd /var/www/ && git clone https://github.com/iqrfsdk/iqrf-daemon-webapp
	# Download composer
	cd /var/www/iqrf-daemon-webapp/install && sh install-composer.sh
	# Download php dependencies
	cd /var/www/iqrf-daemon-webapp && php install/composer.phar install
	# Disable default virtualhost
	rm -f /etc/nginx/sites-enabled/default
	# Copy virtualhost
	cp nginx/iqrf-daemon-webapp.localhost /etc/nginx/sites-available/iqrf-daemon-webapp.localhost
	# Enable virtualhost
	ln -s /etc/nginx/sites-available/iqrf-daemon-webapp.localhost /etc/nginx/sites-enabled/iqrf-daemon-webapp.localhost
	# Restart nginx
	service nginx restart

install-deps:
	apt-get update
	# Install nginx and php5
	apt-get install php5-fpm php5-curl php5-json nginx-full
