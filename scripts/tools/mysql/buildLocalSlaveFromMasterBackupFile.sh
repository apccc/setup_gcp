#!/bin/bash

#build a local MySQL slave from master backup file

if [ -z "$1" ];then
  echo "File not set!"
  exit 1
fi
<<<<<<< HEAD

if [ ! -f "$1" ];then
  echo "No file found $1"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh

=======
>>>>>>> 31e1422f0cbb1dba0171aea00e862d9ab8fb4061

if [ ! -f "$1" ];then
  echo "No file found $1"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh

#turn off external connections while we work
~/setup_gcp/scripts/buildLocalServer/mysql/set.networking.sh close
sudo /etc/init.d/mysql restart

#turn off any existing slave working
$MY 'STOP SLAVE'

#ingest the data
~/setup_gcp/scripts/tools/mysql/ingestFile.sh "$1"

#setup replication information
~/setup_gcp/scripts/buildLocalServer/mysql/set.replication.server.sh
~/setup_gcp/scripts/buildLocalServer/mysql/set.replication.slavedatabasesfromgz.sh "$1"
~/setup_gcp/scripts/buildLocalServer/mysql/set.replication.slaveposfrommastergz.sh "$1"

#start the slave back up
$MY 'START SLAVE'

#wait for replication to catch up

#open networking back up
~/setup_gcp/scripts/buildLocalServer/mysql/set.networking.sh open
sudo /etc/init.d/mysql restart

exit 0
