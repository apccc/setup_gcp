#!/bin/bash

source ~/setup/settings/core.sh

echo "* Building Apache 2 Web Server for $COMPANY_NAME on $HOSTNAME"
echo "**********************************************"
echo "* Updating apt-get:"
sudo apt-get update

echo "* Installing apache2:"
sudo apt-get install -y apache2

echo "* Installing libapache2-mod-php5:"
sudo apt-get install -y libapache2-mod-php5

#clear out existing sites-enabled files
if [ -d /etc/apache2/sites-enabled ];then
  echo " * Clearing existing files in sites-enabled:"
  sudo rm /etc/apache2/sites-enabled/*
fi

~/setup/scripts/buildLocalServer/apache2Web/setupExtrasetup.conf.sh
~/setup/scripts/buildLocalServer/apache2Web/setupDefaultSite.sh
~/setup/scripts/buildLocalServer/apache2Web/setupServerSites.sh

echo "* Enabling mod_ssl"
sudo a2enmod ssl

echo "* Enabling mod_rewrite"
sudo a2enmod rewrite

echo "* Restarting apache2:"
sudo /etc/init.d/apache2 restart

echo "* Showing current apache2 configuration:"
sudo apache2ctl -S
exit 0
