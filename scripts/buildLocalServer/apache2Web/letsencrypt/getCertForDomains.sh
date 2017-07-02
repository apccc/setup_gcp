#!/bin/bash

source ~/setup/settings/core.sh

#make sure letsencrypt is installed
~/setup/scripts/buildLocalServer/apache2Web/letsencrypt/installLetsencrypt.sh

#primary domain for recording the cert
ZDOMAIN=`echo "$1" | egrep -m1 -oe '[a-z0-9.]*'`
#all domains covered in the cert
ZDOMAINS=`echo "$2" | egrep -m1 -oe '[a-z0-9,.]*'`

if [ -z "$ZDOMAIN" ];then
  echo "Domain Not Set"
  exit 1
fi

if [ -z "$ZDOMAINS" ];then
  echo "Domains Not Set"
  exit 1
fi

if [ -z "$3" ];then
  ZADMINEMAIL="$COMPANY_SYSADMIN_EMAIL"
else
  ZADMINEMAIL=$3
fi

LOGDIRECTORY=/var/log/letsencrypt

echo " * Getting letsencrypt cert for domains."
echo " * Domain $ZDOMAIN Set."
echo " * Domains $ZDOMAINS Set."
echo " * Admin Email $ZADMINEMAIL Set."

#check for log directory
if [ ! -d "$LOGDIRECTORY" ];then
  sudo mkdir "$LOGDIRECTORY"
fi

#stop apache
sudo /etc/init.d/apache2 stop

#get the cert
sudo /root/letsencrypt/letsencrypt-auto certonly --agree-tos --standalone --renew-by-default --text -vvvvvv --email "$ZADMINEMAIL" -d "$ZDOMAINS" 2>&1 | sudo tee -a "${LOGDIRECTORY}/getcert.${ZDOMAIN}.log" > /dev/null
echo " * Check log for more details ${LOGDIRECTORY}/getcert.${ZDOMAIN}.log"

#put the cert in place
~/setup/scripts/buildLocalServer/apache2Web/letsencrypt/updateCertRecordsForDomain.sh "$ZDOMAIN" "$ZDOMAINS"

#start apache
sudo /etc/init.d/apache2 start

exit 0
