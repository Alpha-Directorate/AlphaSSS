#!/bin/bash

if [ ! -f /etc/apache2/sites-available/alphasss.dev.conf ];
then

	# Create database
	mysql -uroot -pvagrant -e "CREATE DATABASE IF NOT EXISTS alphasssdev;"

	# insert data into local database
	mysql -uroot -pvagrant alphasssdev < /var/www/alphasss.dev/vagrant/dump/dump.sql

	# set correct host name
	mysql -uroot -pvagrant -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev/wp' where option_id = 3;"
	mysql -uroot -pvagrant -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev' where option_id = 4;"

	#sudo cp /vagrant/vagrant/etc/nginx/sites-available/default /etc/nginx/sites-available/default

	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available

	sudo a2ensite alphasss.dev

	sudo service apache2 restart
	#sudo service nginx restart
	#sudo service php5-fpm restart
fi