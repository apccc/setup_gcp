#!/bin/bash
source ~/setup-config/setup_gcp/core.sh

PHPINI="/etc/php/7.0/apache2/php.ini"
AUTO_PREPEND_FILE="/var/www/auto_preprend_file.php"

echo " * Configuring php.ini"

#setup the auto_prepend_file
if [ ! -f "$AUTO_PREPEND_FILE" ];then
  F="$AUTO_PREPEND_FILE"
  echo '<?php' | sudo tee $F > /dev/null
  echo '$MYSQL_USER="'"$MYSQL_WEB_USER"'";' | sudo tee -a $F > /dev/null
  echo '$MYSQL_PASS="'"$MYSQL_WEB_USER_PASS"'";' | sudo tee -a $F > /dev/null
fi

#perform updates on php.ini file
TMPFILE=/tmp/setupphpini.txt
cp "$PHPINI" "$TMPFILE"
$FR "auto_prepend_file =" "auto_prepend_file = $AUTO_PREPEND_FILE" "$TMPFILE"
sudo cp "$TMPFILE" "$PHPINI"
rm "$TMPFILE"
exit 0
