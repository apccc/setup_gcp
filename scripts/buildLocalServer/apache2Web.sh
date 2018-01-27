#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo "* Building Apache 2 Web Server for $COMPANY_NAME on $HOSTNAME"
echo "**********************************************"
echo "* Updating apt-get:"
sudo apt-get update -qq

echo "* Installing apache2:"
sudo apt-get install -yqq apache2

echo "* Installing apache2-mod-php7.0:"
sudo apt-get install -yqq apache2-mod-php7.0

echo "* Installing php7.0-mysql:"
sudo apt-get install -yqq php7.0-mysql

echo "* Installing php7.0:"
sudo apt-get install -yqq php7.0

echo "* Installing php-curl"
sudo apt-get install -yqq php-curl

#clear out existing sites-enabled files
if [ -d /etc/apache2/sites-enabled ];then
  echo " * Clearing existing files in sites-enabled:"
  sudo rm /etc/apache2/sites-enabled/*
fi

~/setup_gcp/scripts/buildLocalServer/apache2Web/setupExtrasetup.conf.sh
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupPHP.ini.sh
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupDefaultSite.sh
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupServerSites.sh

echo "* Enabling mod_ssl"
sudo a2enmod ssl

echo "* Enabling mod_rewrite"
sudo a2enmod rewrite

echo "* Restarting apache2:"
sudo /etc/init.d/apache2 restart

echo "* Showing current apache2 configuration:"
sudo apache2ctl -S
exit 0
