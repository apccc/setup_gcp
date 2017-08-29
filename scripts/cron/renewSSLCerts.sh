#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Renewing SSL Certs"

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="T" WHERE `SSL`="T"'

~/setup_gcp/scripts/buildLocalServer/apache2Web/setupDefaultSite.sh
~/setup_gcp/scripts/buildLocalServer/apache2Web/setupServerSites.sh

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="F" WHERE `renew_SSL`="T"'

exit 0
