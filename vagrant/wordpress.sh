#!/bin/bash

if [ ! -f /etc/apache2/sites-available/alphasss.dev.conf ];
then

	# Create database
	mysql -uroot -pvagrant -e "CREATE DATABASE IF NOT EXISTS alphasssdev;"

	cat /var/www/alphasss.dev/vagrant/dump/dump_sql_* > /var/www/alphasss.dev/vagrant/dump/dump.sql

	mysql -uroot -pvagrant alphasssdev < /var/www/alphasss.dev/vagrant/dump/dump.sql

	# insert data into local database
	mysql -uroot -pvagrant alphasssdev < /var/www/alphasss.dev/vagrant/dump/dump.sql

	# set correct host name
	mysql -uroot -pvagrant -e "use alphasssdev; update wp_options set option_value = 'https://alphasss.dev/wp' where option_id = 3;"
	mysql -uroot -pvagrant -e "use alphasssdev; update wp_options set option_value = 'https://alphasss.dev' where option_id = 4;"

	sudo rm /etc/apache2/sites-available/default-ssl.conf
	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available
	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available

	sudo a2ensite alphasss.dev
	sudo a2ensite default-ssl

	# Install composer
	curl -sS https://getcomposer.org/installer | php
	sudo mv composer.phar /usr/local/bin/composer

	cd /var/www/alphasss.dev

	composer install

	sudo ./vendor/bin/phpcs --config-set installed_paths /var/www/alphasss.dev/vendor/wp-coding-standards/wpcs/

	sudo rm /etc/hosts

	export WP_LOCAL_DEV=true
    export DB_NAME=alphasssdev
    export DB_USER=root
    export DB_PASSWORD=vagrant
    export DB_HOST=localhost
    export WP_DEBUG=true
    export WP_HOST=alphasss.dev

	sudo service apache2 restart
	#sudo service nginx restart
	#sudo service php5-fpm restart
fi