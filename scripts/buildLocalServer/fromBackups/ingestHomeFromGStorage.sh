#!/bin/bash

#ingest the home directory from a Google Storage backup file

if [ -z "$1" ];then
  echo " * GS backup file not set!"
  exit 1
fi

GSBACKUPFILEFULLPATH="$1"

echo " * Ingesting home directory from GS backup file $GSBACKUPFILEFULLPATH"

GSBACKUPPATH=`echo "$GSBACKUPFILEFULLPATH" | cut -d'/' -f3,4`
if [ -z "$GSBACKUPPATH" ];then
  echo " * GS backup path unrecognized!"
  exit 1
fi

~/setup_gcp/scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh "$GSBACKUPPATH" ~/

echo " * Done ingesting home directory from GS backup file $GSBACKUPFILE"

exit 0
