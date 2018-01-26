#!/bin/bash

#build local server from backup files

if [ -z "$1" ];then
  echo "zstore not set!"
  exit 1
fi
ZSTORE=echo "$1" | egrep -oe '[0-9a-zA-Z_]*'
FOLDERS="$2"

echo " * Building local server from $ZSTORE backups!"

MOST_RECENT_HOME_BACKUP=`gsutil ls -l "gs://$ZSTORE_home/" | egrep -ve '^TOTAL:' | awk '{print $2," ",$3}' | sort | tail -n 1 | awk '{print $2}'`
if [ ! -z "$MOST_RECENT_HOME_BACKUP" ];then
  echo " * Most recent home backup found: $MOST_RECENT_HOME_BACKUP"
fi

echo " * Done building local server from $ZSTORE backups!"
exit 0
