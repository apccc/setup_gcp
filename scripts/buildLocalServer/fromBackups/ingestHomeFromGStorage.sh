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

TMPDIR=/tmp/ingesthomefromgstore
if [ ! -d "$TMPDIR" ];then
  mkdir "$TMPDIR"
fi
~/setup_gcp/scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh "$GSBACKUPPATH" "$TMPDIR"

SUBFOLDER=`ls -1 "$TMPDIR" | head -n 1`
if [ ! -z "$SUBFOLDER" ] && [ -d "${TMPDIR}/${SUBFOLDER}" ];then
  SOURCEHOMEPATH="${TMPDIR}/${SUBFOLDER}"
  for F in `ls -1 "$SOURCEHOMEPATH"`;do
    DEST=~/$F
    if [ -d "$DEST" ];then
      echo " * $DEST exists (directory) skipping..."
      continue
    fi
    if [ -f "$DEST" ];then
      echo " * $DEST exists (file) skipping..."
      continue
    fi
    echo " * Moving $F to $DEST"
    mv "${SOURCEHOMEPATH}/${F}" "$DEST"
  done
fi

if [ -d "$TMPDIR" ];then
  rm -rf "$TMPDIR"
fi

echo " * Done ingesting home directory from GS backup file $GSBACKUPFILE"

exit 0
