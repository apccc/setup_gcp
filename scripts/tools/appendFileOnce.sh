#!/bin/bash

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

##OPTIONAL VARIABLE
LINE=$3

#make sure the file exists
if [ ! -f $FILE ];then
        echo "File \"$FILE\" does not exist. Touching it."
        touch $FILE
fi

#check if string is already in file
FIND=`~/setup_gcp/scripts/tools/escapeStringForPcregrep.sh "$STRING"`
FPCREGREP=/tmp/f.fpcregrep
echo "$FIND" > $FPCREGREP
if [ `/usr/bin/pcregrep -c -M -f $FPCREGREP $FILE` -lt 1 ];then
        if [ -z "$LINE" ];then
                echo "Appending \"$STRING\" to file \"$FILE\"."
                echo "$STRING" >> $FILE
        else
                F=/tmp/appendFileOnce.tmp
                X2="${LINE}i"`~/setup_gcp/scripts/tools/escapeStringForSed.sh "$STRING"`
                echo $X2
                sed "$X2" $FILE > $F
                if [ `/usr/bin/pcregrep -c -M -f $FPCREGREP $F` -gt 0 ];then
                        echo "Appending file at line $LINE."
                        cat $F > $FILE
                else
                        echo "Error: Could not append file at line $LINE."
                fi
                rm $F
        fi
else
        echo "String already exists: \"$STRING\"."
fi

rm $FPCREGREP

exit 0
