#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Renewing SSL Certs"

#ensure this hasn't been run in the last week
if [ `$CC renewSSLCerts 20 1` != 'ok' ];then
  echo " * renewSSLCerts check out has not expired!"
  exit 1
fi

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="T" WHERE `SSL`="T"'

~/setup_gcp/scripts/buildLocalServer/apache2Web/setupDefaultSite.sh
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupServerSites.sh

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="F" WHERE `renew_SSL`="T"'

exit 0
