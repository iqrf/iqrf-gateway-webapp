all: install

install:
	cd /var/www/ && git clone https://github.com/iqrfsdk/iqrf-daemon-webapp
	rm -f /etc/nginx/sites-enabled/default
	cp nginx/iqrf-daemon-webapp.localhost /etc/nginx/sites-available/iqrf-daemon-webapp.localhost
	ln -s /etc/nginx/sites-available/iqrf-daemon-webapp.localhost /etc/nginx/sites-enabled/iqrf-daemon-webapp.localhost
	service nginx restart
