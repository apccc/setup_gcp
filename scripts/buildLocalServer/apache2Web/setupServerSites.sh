#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Setting up Server Sites"

for SERVERWEBSITEID in $($MY 'SELECT `id` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`!="'"${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"'" AND `active`="T" ORDER BY `subdomain` ASC' | tail -n +2);do
  SUBDOMAIN=$($MY 'SELECT `subdomain` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `id`="'"$SERVERWEBSITEID"'" LIMIT 1' | tail -n 1)
  ~/setup_gcp/scripts/buildLocalServer/apache2Web/setupSite.sh "${SUBDOMAIN}"
done

exit 0
