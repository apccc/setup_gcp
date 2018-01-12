#!/bin/bash

#set the networking

if [ ! -z "$1" ] && [ "$1" == "open" ];then
  BINDADDRESS='*'
else
  BINDADDRESS='127.0.0.1'
fi

F="/etc/mysql/setup.conf.d/networking.cnf"
echo " * Setting bind-address to $BINDADDRESS in $F"

#make sure the setup.conf.d directory is setup
~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh

echo "[mysqld]" | sudo tee "$F" > /dev/null
echo "bind-address=$BINDADDRESS" | sudo tee -a "$F" > /dev/null

exit 0
