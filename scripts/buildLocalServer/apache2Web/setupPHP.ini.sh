#!/bin/bash
source ~/setup-config/setup_gcp/core.sh

PHPINI=`sudo find /etc/ -name "php.ini" | grep 'apache' | head -n 1`
AUTO_PREPEND_FILE="/var/www/auto_preprend_file.php"

if [ ! -f "$PHPINI" ];then
  echo "Error: Could not find php.ini at $PHPINI"
  exit 1
fi

echo " * Configuring php.ini"

#setup the auto_prepend_file
F="$AUTO_PREPEND_FILE"
echo '<?php' | sudo tee "$F" > /dev/null
echo '$SYSTEM_DATABASE="'"$SYSTEM_DATABASE"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_USER="'"$MYSQL_WEB_USER"'";' | sudo tee -a "$F" > /dev/null
echo '$MYSQL_PASS="'"$MYSQL_WEB_USER_PASS"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SITE_KEY="'"$RECAPTCHA_SITE_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$RECAPTCHA_SECRET_KEY="'"$RECAPTCHA_SECRET_KEY"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_USERNAME="'"$MAILGUN_SMTP_USERNAME"'";' | sudo tee -a "$F" > /dev/null
echo '$MAILGUN_SMTP_PASSWORD="'"$MAILGUN_SMTP_PASSWORD"'";' | sudo tee -a "$F" > /dev/null
echo '?>' | sudo tee -a $F > /dev/null

#perform updates on php.ini file
TMPFILE=/tmp/setupphpini.txt
cp "$PHPINI" "$TMPFILE"
$FR "auto_prepend_file =" "auto_prepend_file = $AUTO_PREPEND_FILE" "$TMPFILE"
sudo cp "$TMPFILE" "$PHPINI"
rm "$TMPFILE"
exit 0
