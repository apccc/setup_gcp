#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Installing Default Site Web Files"

D="/var/www/${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"
echo " * Setting up site files at $D"
sudo rsync -av ~/setup_gcp/settings/server/defaultSiteTemplate/htdocs "$D/"

F="$D/settings.php"
echo " * Setting up $F"
echo '<?php' | sudo tee "$F" > /dev/null
echo '$COMPANY_NAME="'"$COMPANY_NAME"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_DOMAIN="'"$COMPANY_DOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_ADMIN_SUBDOMAIN="'"$COMPANY_ADMIN_SUBDOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PROTOCOL="https";' | sudo tee -a "$F" > /dev/null
echo '$SITE_CONTROL_DOMAIN="'"$COMPANY_ADMIN_SUBDOMAIN"'.'"$COMPANY_DOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_TOS_URL="'"$SITE_TOS_URL"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PRIVACY_URL="'"$SITE_PRIVACY_URL"'";' | sudo tee -a "$F" > /dev/null
echo '?>' | sudo tee -a "$F" > /dev/null

echo " * Done Installing Default Site Web Files"

exit 0
