#!/bin/bash

source ~/setup_gcp/settings/core.sh

echo "* Building the default site ${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"
echo "****************************"

F=/etc/apache2/sites-enabled/000-default.conf
if [ -f "$F" ];then
  sudo rm "$F"
fi
if [ -f "/var/www/html/index.html" ];then
  sudo rm "/var/www/html/index.html"
fi
if [ -d "/var/www/html" ];then
  sudo rm -rf "/var/www/html"
fi

D="/var/www/${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"
if [ ! -d "$D" ];then
  sudo mkdir "$D"
fi
if [ ! -d "$D/htdocs" ];then
  sudo mkdir "$D/htdocs"
fi

echo " * Installing Web files to ${D}:"
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupDefaultSite/installWebFiles.sh

echo " * Set up Database:"
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupDefaultSite/setupDatabase.sh


~/setup_gcp/scripts/buildLocalServer/apache2Web/setupSite.sh "${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"

echo " * Done building the default site ${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"

exit 0
