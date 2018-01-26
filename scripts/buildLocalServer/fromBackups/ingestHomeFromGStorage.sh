#!/bin/bash

#ingest the home directory from a Google Storage backup file

if [ -z "$1" ];then
  echo " * GS backup file not set!"
  exit 1
fi

GSBACKUPFILEFULLPATH="$1"

echo " * Ingesting home directory from GS backup file $GSBACKUPFILEFULLPATH"

~/setup_gcp/scripts/buildLocalServer/fromBackups/ingestFolderSkipExisting.sh "$GSBACKUPFILEFULLPATH" ~

echo " * Done ingesting home directory from GS backup file $GSBACKUPFILEFULLPATH"

exit 0
