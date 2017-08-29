#!/bin/bash

source ~/setup/settings/core.sh

mysqlPass=`~/setup_gcp/settings/get/mysql_r_pw.sh`

if [[ ! $1 ]];then
  echo '* mysql command server 1 link error - command not set'
  exit 0
fi

echo "$1" | mysql -u root -p$mysqlPass --host "$MYSQL_1_HOST"
