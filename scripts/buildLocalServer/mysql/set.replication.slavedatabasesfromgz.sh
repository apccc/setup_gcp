#!/bin/bash
if [ -z "$1" ];then
  echo " * setReplicationDatabasesFromGZBackup - File not set!"
  exit 1
fi

#set file path
F=$1

if [ ! -f "$F" ];then
  echo " * setReplicationDatabasesFromGZBackup - File not found!"
  exit 1
fi

#config variables
CONFIGFILEPATH='/etc/mysql/setup.conf.d/replication_dbs.cnf'

#make sure the setup.conf.d directory is setup
~/setup_gcp/scripts/buildLocalServer/mysql/setup.conf.d.sh

echo " * Setting up $CONFIGFILEPATH from $F"
echo "[mysqld]" | sudo tee "$CONFIGFILEPATH" > /dev/null

#loop through databases in backup file
for D in $(zcat "$F" | egrep -e '^CREATE DATABASE' | egrep -oe '`[a-zA-Z0-9_]*`' | egrep -oe '[a-zA-Z0-9_]*');do
  echo "replicate-wild-do-table=${D}.%" | sudo tee -a "$CONFIGFILEPATH" > /dev/null
done

exit 0;
