#!/bin/bash

#build local server from backup files

if [ -z "$1" ];then
  echo "zstore not set!"
  exit 1
fi
ZSTORE=`echo "$1" | egrep -oe '[0-9a-zA-Z_]*'`
FOLDERS="$2"

echo " * Building local server from $ZSTORE backups!"

#grab the home directory from backup
MOST_RECENT_HOME_BACKUP=`gsutil ls -l "gs://${ZSTORE}_home/" | egrep -ve '^TOTAL:' | awk '{print $2," ",$3}' | sort | tail -n 1 | awk '{print $2}'`
if [ ! -z "$MOST_RECENT_HOME_BACKUP" ];then
  echo " * Most recent home backup found: $MOST_RECENT_HOME_BACKUP"
  ~/setup_gcp/scripts/buildLocalServer/fromBackups/ingestHomeFromGStorage.sh "$MOST_RECENT_HOME_BACKUP"
fi

#ingest the SSL certs, if there are any
if [ -d ~/ssl_backup ];then
  echo " * SSL certs found, will restore"
  sudo mkdir /root/ssl 2>/dev/null
  sudo rsync -av ~/ssl_backup/ /root/ssl/
  sudo chown -R root:root /root/ssl/
fi

#ingest additional folders, if set to
if [ ! -z "$FOLDERS" ];then
  echo " * Grabbing additional folders from backup:"
  for FOLDER in `echo "$FOLDERS" | egrep -oe '[a-zA-Z0-9_./-]*'`;do
    echo " * Grabbing folder $FOLDER"
    REMOTEFOLDER=`echo "$FOLDER" | tr '/' '_'`
    sudo mkdir -p "$FOLDER" 2>/dev/null
    M=`whoami`
    sudo chown ${M}:${M} "$FOLDER"
    GSBACKUPFILEFULLPATH=`gsutil ls -l "gs://${ZSTORE}${REMOTEFOLDER}/" | egrep -ve '^TOTAL:' | awk '{print $2," ",$3}' | sort | tail -n 1 | awk '{print $2}'`
    if [ ! -z "$GSBACKUPFILEFULLPATH" ];then
      echo " * Transferring $GSBACKUPFILEFULLPATH to $FOLDER"
      ~/setup_gcp/scripts/buildLocalServer/fromBackups/ingestFolderSkipExisting.sh "$GSBACKUPFILEFULLPATH" "$FOLDER"
    fi
  done
fi

echo " * Done building local server from $ZSTORE backups!"
exit 0
