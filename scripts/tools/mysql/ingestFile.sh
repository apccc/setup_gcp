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



echo "Ingesting MySQL File"
exit 0
