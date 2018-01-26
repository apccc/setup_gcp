#!/bin/bash

#ingest a directory from a Google Storage backup file

if [ -z "$1" ];then
  echo " * GS backup file not set!"
  exit 1
fi

if [ -z "$2" ];then
  echo " * Destination directory not set!"
  exit 1
fi

DESTDIR="$2"
if [ ! -d "$2" ];then
  echo " * Directory not found! $DESTDIR"
  exit 1
fi

GSBACKUPFILEFULLPATH="$1"

echo " * Ingesting directory from GS backup file $GSBACKUPFILEFULLPATH to $DESTDIR"

GSBACKUPPATH=`echo "$GSBACKUPFILEFULLPATH" | cut -d'/' -f3,4`
if [ -z "$GSBACKUPPATH" ];then
  echo " * GS backup path unrecognized!"
  exit 1
fi

TMPDIR=/tmp/ingestdirfromgstore
if [ ! -d "$TMPDIR" ];then
  mkdir "$TMPDIR"
fi
~/setup_gcp/scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh "$GSBACKUPPATH" "$TMPDIR"

SUBFOLDER=`ls -1 "$TMPDIR" | head -n 1`
if [ ! -z "$SUBFOLDER" ] && [ -d "${TMPDIR}/${SUBFOLDER}" ];then
  SOURCEPATH="${TMPDIR}/${SUBFOLDER}"
  for F in `ls -1 "$SOURCEPATH"`;do
    DEST="${DESTDIR}/${F}"
    if [ -d "$DEST" ];then
      echo " * $DEST exists (directory) skipping..."
      continue
    fi
    if [ -f "$DEST" ];then
      echo " * $DEST exists (file) skipping..."
      continue
    fi
    echo " * Moving $F to $DEST"
    mv "${SOURCEPATH}/${F}" "$DEST"
  done
fi

if [ -d "$TMPDIR" ];then
  rm -rf "$TMPDIR"
fi

echo " * Done ingesting directory from GS backup file $GSBACKUPFILE"

exit 0
