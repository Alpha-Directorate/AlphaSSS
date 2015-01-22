#!/bin/bash

if [ ! -f /var/www/alphasss.dev/local-config.php ];
then

	# Create database
	mysql -uroot -pvagrant -e "CREATE DATABASE IF NOT EXISTS alphasocialdev;"

	# insert data into local database
	mysql -uroot -pvagrant alphasocialdev < /var/www/alphasss.dev/vagrant/dump/dump.sql

	# set correct host name
	mysql -uroot -pvagrant -e "use alphasocialdev; update wp_options set option_value = 'http://alphasss.dev/wp' where option_id = 3;"
	mysql -uroot -pvagrant -e "use alphasocialdev; update wp_options set option_value = 'http://alphasss.dev' where option_id = 4;"

	#sudo cp /vagrant/vagrant/etc/nginx/sites-available/default /etc/nginx/sites-available/default

	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available

	sudo a2ensite alphasss.dev

	sudo cp /var/www/alphasss.dev/vagrant/var/www/alphasss.dev/local-config.php /var/www/alphasss.dev/local-config.php

	sudo service apache2 restart
	#sudo service nginx restart
	#sudo service php5-fpm restart
fi