#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

#make sure letsencrypt is installed
~/setup_gcp/scripts/buildLocalServer/apache2Web/letsencrypt/installLetsencrypt.sh

ZDOMAIN=`echo "$1" | egrep -m1 -oe '[a-z0-9.]*'`

if [ -z "$ZDOMAIN" ];then
  echo "Domain Not Set"
  exit 1
fi

if [ -z "$2" ];then
  ZADMINEMAIL="$COMPANY_SYSADMIN_EMAIL"
else
  ZADMINEMAIL="$2"
fi

LOGDIRECTORY=/var/log/letsencrypt

echo " * Getting letsencrypt cert for domain."
echo " * Domain $ZDOMAIN Set."
echo " * Admin Email $ZADMINEMAIL Set."

#check for log directory
if [[ `sudo ls -1 "$LOGDIRECTORY" | wc -l` -lt 1 ]];then
  echo " * Creating $LOGDIRECTORY"
  sudo mkdir "$LOGDIRECTORY"
fi

#stop apache
sudo /etc/init.d/apache2 stop

#get the cert
sudo /root/letsencrypt/letsencrypt-auto certonly --agree-tos --standalone --renew-by-default --text -vvvvvv --email "$ZADMINEMAIL" -d "$ZDOMAIN" 2>&1 | sudo tee -a "${LOGDIRECTORY}/getcert.${ZDOMAIN}.log" > /dev/null
echo " * Check log for more details ${LOGDIRECTORY}/getcert.${ZDOMAIN}.log"

#put the cert in place
~/setup_gcp/scripts/buildLocalServer/apache2Web/letsencrypt/updateCertRecordsForDomain.sh "$ZDOMAIN"

#start apache
sudo /etc/init.d/apache2 start

exit 0
