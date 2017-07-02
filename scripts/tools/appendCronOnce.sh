#!/bin/bash

CHECK=$1
if [ -z "$CHECK" ];then
        echo "CHECK string not set."
        exit
fi

STRING=$2
if [ -z "$STRING" ];then
        STRING="$CHECK"
fi

USER=$3
if [ -z "$USER" ];then
        echo "Updating crontab."
        CRONTAB="crontab"
else
        echo "Updating $USER crontab."
        CRONTAB="crontab -u $USER"
fi


#ADD NTPDATE TO THE CRON
FIND=`~/setup/scripts/tools/escapeStringForPcregrep.sh "$CHECK"`
FPCREGREP=/tmp/ac.find.fpcregrep
CRONPCREGREP=/tmp/ac.cron.fpcregrep
$CRONTAB -l > $CRONPCREGREP
echo "$FIND" > $FPCREGREP

if [ `/usr/bin/pcregrep -c -M -f $FPCREGREP $CRONPCREGREP` -lt 1 ];then
        echo "Adding line to cron: \"$STRING\"."
        ($CRONTAB -l; echo "$STRING") | $CRONTAB -
else
        echo "Cron line already exists \"$STRING\"."
fi

rm $CRONPCREGREP
rm $FPCREGREP

exit 0
