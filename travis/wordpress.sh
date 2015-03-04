#!/bin/bash

# Create database
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS alphasssdev;"

cat ./vagrant/dump/dump_sql_* > ./vagrant/dump/dump.sql

# insert data into local database
mysql -uroot alphasssdev < ./vagrant/dump/dump.sql

# set correct host name
mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'https://alphasss.dev/wp' where option_id = 3;"
mysql -uroot -e "use alphasssdev; update wp_options set option_value = 'https://alphasss.dev' where option_id = 4;"

# Enable apache mods
sudo a2enmod rewrite
sudo a2enmod ssl
#--

sudo mkdir /etc/apache2/ssl

sudo cp ./vagrant/etc/apache2/ssl/apache.key /etc/apache2/ssl
sudo cp ./vagrant/etc/apache2/ssl/apache.crt /etc/apache2/ssl

sudo rm /etc/apache2/sites-available/default
sudo mv ./travis/etc/apache2/sites-available/alphasss.dev.conf /etc/apache2/sites-available/default

sudo rm /etc/apache2/sites-available/default-ssl.conf
sudo cp ./vagrant/etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available

sudo a2ensite alphasss.dev
sudo a2ensite default-ssl

# Install composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

composer install --dev

sudo service apache2 restart