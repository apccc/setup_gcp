#!/bin/bash

#set the local database replication position

if [ -z "$1" ];then
  echo " * set.replication.slaveposfrommastergz - File not set!"
  exit 1
fi

#set file path
F=$1

if [ ! -f "$F" ];then
  echo " * set.replication.slaveposfrommastergz - File not found!"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh

if [ -z "$MYSQL_REPLIC_MASTER" ];then
  echo " * Error: MYSQL_REPLIC_MASTER not set!"
  exit 1
fi

CONFIGINFO=`zcat "$F" | head | egrep -oe 'mysql.*' | sed 's/\s/ /g'`
SLAVEMASTERFILE=`echo "$CONFIGINFO" | cut -d' ' -f1`
SLAVEMASTERPOS=`echo "$CONFIGINFO" | cut -d' ' -f2`

#update master data
echo " * Local slave replication set "
X='CHANGE MASTER TO MASTER_HOST=\''"$MYSQL_REPLIC_MASTER"'\',MASTER_USER=\''"$MYSQL_REPLIC_USER"'\',MASTER_PASSWORD=\''"$MYSQL_REPLIC_PASS"'\',MASTER_PORT=3306,MASTER_LOG_FILE=\''"$SLAVEMASTERFILE"'\',MASTER_LOG_POS='"$SLAVEMASTERPOS"
echo "$X"
#$MY "$X"
exit 0
