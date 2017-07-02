#!/bin/bash

source ~/setup/settings/core.sh

echo " * Renewing SSL Certs"

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="T" WHERE `SSL`="T"'

~/setup/scripts/buildLocalServer/apache2Web/setupDefaultSite.sh
~/setup/scripts/buildLocalServer/apache2Web/setupServerSites.sh

$MY 'UPDATE `'"$SYSTEM_DATABASE"'`.`sites` SET `renew_SSL`="F" WHERE `renew_SSL`="T"'

exit 0