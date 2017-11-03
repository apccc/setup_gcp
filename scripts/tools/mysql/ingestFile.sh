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

<<<<<<< HEAD
FILEEXT="${DOWNLOADEDTMPFILE##*.}"
=======
FILEEXT="${BASEFILENAME##*.}"
>>>>>>> 1cc278db454e8757a4e48f47fa9e0184b9ee6bea
echo "File extension found: $FILEEXT"



echo "Ingesting MySQL File"
exit 0
