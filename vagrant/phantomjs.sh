#!/bin/bash

# Download phantomjs. I don't use last version becuase there is
# no good support for linux yet, please update it to version 2.0 when
# it became available
wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-1.9.8-linux-x86_64.tar.bz2

# Unpack phantomjs
tar -xvf phantomjs-1.9.8-linux-x86_64.tar.bz2

# copy phantomjs to executable
sudo mv phantomjs-1.9.8-linux-x86_64/bin/phantomjs /usr/local/bin

# remove installation files
rm phantomjs-1.9.8-linux-x86_64.tar.bz2
rm -rf phantomjs-1.9.8-linux-x86_64

# copy run scripts
sudo cp /var/www/alphasss.dev/vagrant/etc/default/phantomjs /etc/default/
sudo cp /var/www/alphasss.dev/vagrant/etc/init.d/phantomjs /etc/init.d/

sudo chmod +x /etc/init.d/phantomjs

# service to start at boot time
sudo update-rc.d phantomjs defaults

sudo service phantomjs start

