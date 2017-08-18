#!/bin/bash

#compress directory to tar.gz

#input directory path
INPUTDIR=$1
if [ -z $INPUTDIR ];then
  echo "Input Directory Not Set!"
  exit 0
fi
if [ ! -d $INPUTDIR ];then
  echo "Input Directory Not Found!"
  exit 0
fi

#output file path
OUTPUTFILE=$2
if [ -z $OUTPUTFILE ];then
  echo "Output File Not Set!"
  exit 0
fi

ZDIRNAME=`dirname "$INPUTDIR"`
ZBASENAME=`basename "$INPUTDIR"`

cd "$ZDIRNAME"
echo "Compressing ${INPUTDIR} to ${OUTPUTFILE}..."
tar -zcf "$OUTPUTFILE" "$ZBASENAME"
echo "Done compressing."
exit 0
