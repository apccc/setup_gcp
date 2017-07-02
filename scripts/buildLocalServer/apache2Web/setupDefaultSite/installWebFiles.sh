#!/bin/bash

source ~/setup/settings/core.sh

echo " * Installing Default Site Web Files"

D="/var/www/${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"
echo " * Setting up site files at $D"
sudo rsync -av ~/setup/settings/server/defaultSiteTemplate/htdocs "$D/"

F="$D/settings.php"
echo " * Setting up $F"
echo '<?php' | sudo tee "$F" > /dev/null
echo '$COMPANY_NAME="'"$COMPANY_NAME"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_DOMAIN="'"$COMPANY_DOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_ADMIN_SUBDOMAIN="'"$COMPANY_ADMIN_SUBDOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SYSTEM_DATABASE="'"$SYSTEM_DATABASE"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_USER="'"$MYSQL_WEB_USER"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_PASS="'"$MYSQL_WEB_USER_PASS"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PROTOCOL="https";' | sudo tee -a "$F" > /dev/null
echo '$SITE_CONTROL_DOMAIN="'"$COMPANY_ADMIN_SUBDOMAIN"'.'"$COMPANY_DOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_TOS_URL="'"$SITE_TOS_URL"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PRIVACY_URL="'"$SITE_PRIVACY_URL"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SITE_KEY="'"$RECAPTCHA_SITE_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SECRET_KEY="'"$RECAPTCHA_SECRET_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_USERNAME="'"$MAILGUN_SMTP_USERNAME"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_PASSWORD="'"$MAILGUN_SMTP_PASSWORD"'";' | sudo tee -a "$F" > /dev/null
echo '?>' | sudo tee -a "$F" > /dev/null

echo " * Done Installing Default Site Web Files"

exit 0
