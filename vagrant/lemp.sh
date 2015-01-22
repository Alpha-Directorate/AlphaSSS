#!/bin/bash

if [ ! -f /var/www/alphasss.dev/local-config.php ];
then
	sudo echo 'mysql-server mysql-server/root_password password vagrant' | debconf-set-selections
	sudo echo 'mysql-server mysql-server/root_password_again password vagrant' | debconf-set-selections

	sudo apt-get install -y mysql-server mysql-client php5-mysql libapache2-mod-auth-mysql

	sudo apt-get install -y php5 libapache2-mod-php5 php5-mcrypt

	sudo apt-get install -y apache2

	sudo service apache2 restart

	sudo a2enmod rewrite

#	echo "deb http://ppa.launchpad.net/nginx/stable/ubuntu $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/nginx-stable.list
#	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C300EE8C
#	sudo apt-get update
#	sudo apt-get install -y nginx
#
#	sudo service nginx start
#
#	sudo apt-get install -y php5-fpm
#
#	sudo cp /vagrant/vagrant/etc/php5/fpm/php.ini /etc/php5/fpm/php.ini
fi