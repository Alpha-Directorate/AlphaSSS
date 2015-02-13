#!/bin/bash

# Create database
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS alphasssdev;"

cat ./vagrant/dump/dump_sql_* > dump.sql

# insert data into local database
mysql -uroot alphasssdev < ./vagrant/dump/dump.sql

# set correct host name
mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev/wp' where option_id = 3;"
mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'http://alphasss.dev' where option_id = 4;"

sudo rm /etc/apache2/sites-available/default
sudo mv ./travis/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available/default

# Install composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

composer install --dev

sudo service apache2 restart