#!/bin/bash

FIND=$1

if [ -z "$FIND" ];then
   echo "FIND variable unset! Aborting"
   exit 1
fi

REPLACE=$2

if [ -z "$REPLACE" ];then
   echo "REPLACE variable unset! Aborting"
   exit 1
fi

FILE=$3

if [ -z $FILE ];then
   echo "FILE variable unset! Aborting"
   exit 1
fi

if [ ! -f $FILE ];then
	echo "File \"$FILE\" does not exist!"
	exit 1
fi

F=`~/setup/scripts/tools/escapeStringForPcregrep.sh "$FIND"`
R=`~/setup/scripts/tools/escapeStringForPcregrep.sh "$REPLACE"`
FPCREGREP=/tmp/f.fpcregrep
RPCREGREP=/tmp/r.fpcregrep
echo "$F" > $FPCREGREP
echo "$R" > $RPCREGREP
if [ `/usr/bin/pcregrep -c -M -f $FPCREGREP $FILE` -eq 1 ];then
	if [ `/usr/bin/pcregrep -c -M -f $RPCREGREP $FILE` -lt 1 ];then
	        F=`~/setup/scripts/tools/escapeStringForSed.sh "$FIND"`
		R=`~/setup/scripts/tools/escapeStringForSed.sh "$REPLACE"`
		echo ""
		echo "Find and replace in progress on \"$FILE\"."
		TMPSED=/tmp/cmds.sed
		echo '1h; 1!H; ${ g; s/'"$F"'/'"$R"'/; p; }' > $TMPSED
                cat $TMPSED
                sed -i -n -f $TMPSED $FILE
		if [ -f $TMPSED ];then
			rm $TMPSED
		fi
		echo ""
	else
                echo "findreplace error: \"$FILE\" replace string already exists in file:"
                cat $RPCREGREP
                echo ""
	fi
else
        echo "findreplace error: \"$FILE\" find string did not match exactly once:"
        cat $FPCREGREP
        echo ""
fi

if [ -f $FPCREGREP ];then
        rm $FPCREGREP
fi

if [ -f $RPCREGREP ];then
        rm $RPCREGREP
fi
exit 0
