#!/bin/bash

if [ -z "$1" ];then
  echo "File not set!"
  exit 1
fi

if [ ! -f "$1" ];then
  echo "No file found $1"
  exit 1
fi

INCOMINGFILE="$1"

BASEFILENAME=`basename ${INCOMINGFILE}`
echo "File found: $BASEFILENAME"

FILEEXT="${BASEFILENAME##*.}"
echo "File extension found: $FILEEXT"

if [ "$FILEEXT" == 'gz' ];then
  zcat "$INCOMINGFILE" | mysql -u root -p
else
  MYSQLINPUTFILEPATH="${INCOMINGFILE}"
fi

echo "Ingesting MySQL File: $MYSQLINPUTFILEPATH"

#cleanup tmp folder
if [ -d "$TMPFILEFOLDER" ];then
  echo "Removing $TMPFILEFOLDER"
  rm -rf "$TMPFILEFOLDER"
fi

exit 0
