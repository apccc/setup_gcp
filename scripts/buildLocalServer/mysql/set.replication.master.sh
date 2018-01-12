#!/bin/bash

#set the database server to act as a replication master

#begin setting up the replication master
F='/etc/mysql/setup.conf.d/replicationmaster.cnf'
if [ ! -f "$F" ];then
  echo " * Setting up server database replication as master."

  #make sure the setup.conf.d directory is setup
  ~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh
  echo " * Creating $F"
  echo "[mysqld]" | sudo tee "$F" > /dev/null
  echo "log-bin" | sudo tee -a "$F" > /dev/null
  SERVERID=`echo $((1 + RANDOM % 1000000))`
  echo "server_id=$SERVERID" | sudo tee -a "$F" > /dev/null
fi

exit 0
