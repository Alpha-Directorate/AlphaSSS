#!/bin/bash

if [ ! -f /etc/apache2/sites-available/alphasss.dev.conf ];
then

	# Create database
	mysql -uroot -e "CREATE DATABASE IF NOT EXISTS alphasssdev;"

	# insert data into local database
	mysql -uroot alphasssdev < ./vagrant/dump/dump.sql

	# set correct host name
	mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev/wp' where option_id = 3;"
	mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev' where option_id = 4;"

	sudo cp ./travis/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available
	sudo cp ./travis/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-enabled

	sudo a2ensite alphasss.dev

	# Install composer
	curl -sS https://getcomposer.org/installer | php
	sudo mv composer.phar /usr/local/bin/composer

	composer install --dev

	sudo rm /etc/hosts 

	sudo cp ./travis/etc/hosts /etc/hosts 

	sudo service apache2 restart
fi