#!/bin/bash

if [ ! -z "$1" ];then
  if [ -d "$1" ];then
    D="$1"
    cd "$D"
  fi
fi
if [ -z "$D" ];then
  echo "Directory not set!"
  exit
fi

echo " * Installing Mailgun support."

# Install Composer
curl -sS https://getcomposer.org/installer | php

# Add Mailgun and Guzzle6 as a dependency (see Github README below for more info)
php composer.phar require mailgun/mailgun-php php-http/guzzle6-adapter php-http/message

exit 0
