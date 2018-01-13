#!/bin/bash

#set the database server to act as a replication master

source ~/setup-config/setup_gcp/core.sh

#begin setting up the replication master
F='/etc/mysql/setup.conf.d/replicationmaster.cnf'
if [ ! -f "$F" ];then
  echo " * Setting up server database replication as master."

  #make sure the setup.conf.d directory is setup
  ~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh
  echo " * Creating $F"
  echo "[mysqld]" | sudo tee "$F" > /dev/null
  echo "log-bin" | sudo tee -a "$F" > /dev/null

  #setup as replication member server
  ~/setup_gcp/scripts/buildLocalServer/mysql/set.replication.server.sh
fi

if [ ! -z "${MYSQL_REPLIC_USER}" ] && [[ `$MY "SELECT User FROM mysql.user WHERE User='${MYSQL_REPLIC_USER}'" | wc -l` -lt 1 ]];then
  echo " * Replication user not found, will try to create!"
  $MY "GRANT REPLICATION SLAVE ON *.* TO '${MYSQL_REPLIC_USER}'@'%' IDENTIFIED BY '${MYSQL_REPLIC_PASS}'"
  $MY "FLUSH PRIVILEGES"
fi

exit 0
