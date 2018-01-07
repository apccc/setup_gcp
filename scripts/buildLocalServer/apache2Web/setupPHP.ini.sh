#!/bin/bash
source ~/setup-config/setup_gcp/core.sh

PHPINI="/etc/php/7.0/apache2/php.ini"
AUTO_PREPEND_FILE="/var/www/auto_preprend_file.php"

echo " * Configuring php.ini"

#setup the auto_prepend_file
if [ ! -f "$AUTO_PREPEND_FILE" ];then
  
fi

$FR "auto_prepend_file =" "auto_prepend_file = $AUTO_PREPEND_FILE" "$PHPINI"
exit 0
