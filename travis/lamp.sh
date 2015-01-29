#!/bin/bash

if [ ! -f /etc/apache2/sites-available/alphasss.dev.conf ];
then
	sudo apt-get install -y mysql-client php5-mysql libapache2-mod-auth-mysql

	sudo apt-get install -y php5 libapache2-mod-php5 php5-mcrypt

	sudo apt-get install -y apache2

	sudo service apache2 restart

	sudo a2enmod rewrite
fi