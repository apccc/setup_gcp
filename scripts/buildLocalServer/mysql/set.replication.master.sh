#!/bin/bash

#set the database server to act as a replication master

#make sure the setup.conf.d directory is setup
~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh

#begin setting up the replication master
F='/etc/mysql/setup.conf.d/replicationmaster.cnf'
if [ ! -f "$F" ];then
  echo "Creating $F"
  echo "" | sudo -t "$F"
fi

exit 0
