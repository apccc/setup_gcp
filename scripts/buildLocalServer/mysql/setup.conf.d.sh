#!/bin/bash

#setup the setup.conf.d directory (if necessary)
source ~/setup-config/setup_gcp/core.sh

D="/etc/mysql/setup.conf.d"

if [ ! -d "$D" ];then
  echo "Creating directory $D"
  sudo mkdir "$D"
fi

ADD='!includedir '"${D}/"
TO="/etc/mysql/my.cnf"
TMP="/tmp/tmpsetup_my.cnf"
cp "$TO" "$TMP"
$AP "$ADD" "$TMP"
sudo cp "$TMP" "$TO"
rm "$TMP"

exit 0
