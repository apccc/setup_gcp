#!/bin/bash

#set the networking

if [ ! -z "$1" ] && [ "$1" == "open" ];then
  BINDADDRESS='*'
else
  BINDADDRESS='127.0.0.1'
fi

echo "Setting bind-address to $BINDADDRESS"

F="/etc/mysql/setup.conf.d/networking.cnf"
"[mysqld]"
"bind-address=$BINDADDRESS"

exit 0
