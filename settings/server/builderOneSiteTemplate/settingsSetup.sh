#!/bin/bash
#builderOne

source ~/setup/settings/core.sh

SUBDOMAIN="$1"
if [ -z "$SUBDOMAIN" ];then
  echo " * ERROR: Subdomain not set for settingsSetup"
  exit 1
fi

echo " * Setting up site settings for $SUBDOMAIN"
SITEID=$($MY 'SELECT `id` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
SITEDATABASE=$($MY 'SELECT `database` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
SSL=$($MY 'SELECT `SSL` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
if [ "$SSL" == "T" ];then
  SPROTOCOL="https"
else
  SPROTOCOL="http"
fi

D=/var/www/$SUBDOMAIN
F="$D/settings.php"
echo " * Setting up $F"
echo '<?php' | sudo tee "$F" > /dev/null
echo '$COMPANY_NAME="'"$COMPANY_NAME"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_DOMAIN="'"$COMPANY_DOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$COMPANY_ADMIN_SUBDOMAIN="'"$COMPANY_ADMIN_SUBDOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SYSTEM_DATABASE="'"$SYSTEM_DATABASE"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_DATABASE="'"$SITEDATABASE"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_USER="'"$MYSQL_WEB_USER"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_PASS="'"$MYSQL_WEB_USER_PASS"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PROTOCOL="'"$SPROTOCOL"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_CONTROL_DOMAIN="'"$SUBDOMAIN"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_TOS_URL="'"$SITE_TOS_URL"'";' | sudo tee -a "$F" > /dev/null
echo '$SITE_PRIVACY_URL="'"$SITE_PRIVACY_URL"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SITE_KEY="'"$RECAPTCHA_SITE_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SECRET_KEY="'"$RECAPTCHA_SECRET_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_USERNAME="'"$MAILGUN_SMTP_USERNAME"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_PASSWORD="'"$MAILGUN_SMTP_PASSWORD"'";' | sudo tee -a "$F" > /dev/null
echo '?>' | sudo tee -a "$F" > /dev/null

D=$D/htdocs
F="$D/.htaccess"
echo " * Setting up $F"
echo 'RewriteEngine On' | sudo tee "$F" > /dev/null
echo 'RewriteRule ^log$ /loginPage.php?%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^js/script([0-9-]+).js$ /jsPage.php?q=$1&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^css/style([0-9-]+).css$ /cssPage.php?q=$1&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^favicon.ico$ /a64File.php?f=favicon&t=sites&id='"$SITEID"'&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^afile/([a-z0-9_-]+)/([0-9]+).([a-z0-9_-]+).([a-z0-9]+)$ /a64File.php?f=$3&t=$1&id=$2&ext=$4&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^af/([a-z0-9_-]+)/([0-9]+).([a-z0-9_-]+).([a-z0-9]+)$ /a64FileSite.php?f=$3&t=$1&id=$2&ext=$4&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^accountCreated$ /accountCreatedPage.php?%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^accountActivate/([0-9]+)/([a-zA-Z0-9]+)$ /accountActivatePage.php?userid=$1&hash=$2 [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^resetPassword/([0-9]+)/([a-zA-Z0-9]+)$ /resetPasswordPage.php?userid=$1&hash=$2 [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^$ /index.php?section=index&page=index&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^([a-zA-Z0-9_-]+)$ /index.php?section=$1&page=index&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null
echo 'RewriteRule ^([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+)$ /index.php?section=$1&page=$2&%{QUERY_STRING} [PT,L]' | sudo tee -a "$F" > /dev/null

echo " * Done setting up settingsSetup files for $SUBDOMAIN"

exit 0