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

  TS=`date +%s`
  TMPFILEFOLDER="/tmp/setupgcstrans$TS"

  #make tmp folder
  if [ ! -d "$TMPFILEFOLDER" ];then
    echo "Making $TMPFILEFOLDER"
    mkdir "$TMPFILEFOLDER"
  fi

  echo "Extracting file ${INCOMINGFILE} to ${TMPFILEFOLDER}"
  tar xf "${INCOMINGFILE}" -C "${TMPFILEFOLDER}"

  #cleanup tmp folder
  if [ -d "$TMPFILEFOLDER" ];then
    echo "Removing $TMPFILEFOLDER"
    rm -rf "$TMPFILEFOLDER"
  fi
fi


echo "Ingesting MySQL File"
exit 0
