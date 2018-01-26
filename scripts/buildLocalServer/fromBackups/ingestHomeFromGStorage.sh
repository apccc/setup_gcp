#!/bin/bash

#ingest the home directory from a Google Storage backup file

if [ -z "$1" ];then
  echo " * GS backup file not set!"
  exit 1
fi

GSBACKUPFILE="$1"

echo " * Ingesting home directory from GS backup file $GSBACKUPFILE"

echo " * Done ingesting home directory from GS backup file $GSBACKUPFILE"

exit 0
