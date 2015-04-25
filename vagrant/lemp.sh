#!/bin/bash

if [ ! -f /etc/apache2/sites-available/alphasss.dev.conf ];
then
	sudo echo 'mysql-server mysql-server/root_password password vagrant' | debconf-set-selections
	sudo echo 'mysql-server mysql-server/root_password_again password vagrant' | debconf-set-selections

	sudo apt-get install -y mysql-server mysql-client php5-mysql libapache2-mod-auth-mysql curl

	sudo apt-get install -y php5 libapache2-mod-php5 php5-mcrypt php5-gd php5-curl

	sudo apt-get install -y apache2

	# Enable apache mods
	sudo a2enmod rewrite
	sudo a2enmod ssl
	#--

	sudo mkdir /etc/apache2/ssl

	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/ssl/apache.key /etc/apache2/ssl
	sudo cp /var/www/alphasss.dev/vagrant/etc/apache2/ssl/apache.crt /etc/apache2/ssl

	sudo php5enmod mcrypt

	sudo service apache2 restart

	mkdir ~/scripts

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