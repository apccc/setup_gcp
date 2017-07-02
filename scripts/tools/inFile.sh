#!/bin/bash

#check if a string is in a file

STRING=$1
if [ -z "$STRING" ];then
        echo "STRING variable not set."
        exit
fi

FILE=$2
if [ -z "$FILE" ];then
        echo "FILE variable not set."
        exit
fi

FIND=`~/setup/scripts/tools/escapeStringForPcregrep.sh "$STRING"`
FPCREGREP=/tmp/f.fpcregrep
echo "$FIND" > $FPCREGREP
if [ `/usr/bin/pcregrep -c -M -f $FPCREGREP $FILE` -lt 1 ];then
  echo 0
else
  echo 1
fi

rm $FPCREGREP

exit 0
