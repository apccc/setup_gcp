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
<<<<<<< HEAD
FILEEXT="${DOWNLOADEDTMPFILE##*.}"
=======
FILEEXT="${BASEFILENAME##*.}"
>>>>>>> 1cc278db454e8757a4e48f47fa9e0184b9ee6bea
=======
FILEEXT="${BASEFILENAME##*.}"
>>>>>>> cb59c9a45afa5bb3b5de66c1203c586c0d217fd1
echo "File extension found: $FILEEXT"



echo "Ingesting MySQL File"
exit 0
