#!/bin/bash

#set the database server to be a replication member

source ~/setup-config/setup_gcp/core.sh

#begin setting up the replication member
F='/etc/mysql/setup.conf.d/replication.cnf'
if [ ! -f "$F" ];then
  echo " * Setting up server as replication member."

  #make sure the setup.conf.d directory is setup
  ~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh
  echo " * Creating $F"
  echo "[mysqld]" | sudo tee "$F" > /dev/null
  SERVERID=`echo $((1 + RANDOM % 1000000))`
  echo "server_id=$SERVERID" | sudo tee -a "$F" > /dev/null
fi

exit 0
