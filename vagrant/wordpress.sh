#!/bin/bash

if [ ! -f /var/www/alpha-social.dev/local-config.php ];
then

	# Create database
	mysql -uroot -pvagrant -e "CREATE DATABASE IF NOT EXISTS alphasocialdev;"

	# insert data into local database
	mysql -uroot -pvagrant alphasocialdev < /var/www/alpha-social.dev/vagrant/dump/dump.sql

	# set correct host name
	mysql -uroot -pvagrant -e "use alphasocialdev; update wp_options set option_value = 'http://alpha-social.dev/' where option_id IN (3,4);"


	#sudo cp /vagrant/vagrant/etc/nginx/sites-available/default /etc/nginx/sites-available/default

	sudo cp /var/www/alpha-social.dev/vagrant/etc/apache2/sites-available/alpha-social.dev.conf /etc/apache2/sites-available

	sudo a2ensite alpha-social.dev

	sudo cp /var/www/alpha-social.dev/vagrant/var/www/alpha-social.dev/local-config.php /var/www/alpha-social.dev/local-config.php

	sudo service apache2 restart
	#sudo service nginx restart
	#sudo service php5-fpm restart
fi