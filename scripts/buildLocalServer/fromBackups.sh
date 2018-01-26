#!/bin/bash

#build local server from backup files

if [ -z "$1" ];then
  echo "zstore not set!"
  exit 1
fi
ZSTORE="$1"
FOLDERS="$2"

echo " * Building local server from $ZSTORE backups!"



echo " * Done building local server from $ZSTORE backups!"
exit 0
