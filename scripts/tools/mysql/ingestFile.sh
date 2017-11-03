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

TS=`date +%s`
TMPFILEFOLDER="/tmp/setupmysqlingest$TS"

if [ "$FILEEXT" == 'gz' ];then
  #make tmp folder
  if [ ! -d "$TMPFILEFOLDER" ];then
    echo "Making $TMPFILEFOLDER"
    mkdir "$TMPFILEFOLDER"
  fi

  echo "Extracting file ${INCOMINGFILE} to ${TMPFILEFOLDER}"
  tar xf "${INCOMINGFILE}" -C "${TMPFILEFOLDER}"

  #locate file from tmp folder
  EXTRACTEDFILE=`ls -1 "${TMPFILEFOLDER}/"`
  echo "Extracted file: ${EXTRACTEDFILE}"

  #Use new file
  MYSQLINPUTFILEPATH="${TMPFILEFOLDER}/${EXTRACTEDFILE}"
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
