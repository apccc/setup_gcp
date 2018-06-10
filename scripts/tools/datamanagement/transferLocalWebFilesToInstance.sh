#!/bin/bash

#Transfer local Web files in the /var/www directory to a remote instance

if [ -z "$1" ];then
  echo "Error: Remote instance name not provided."
  exit 1
fi
INSTANCE=`echo "$1" | egrep -oe '[a-zA-Z0-9_.-]*' | head -n 1`
DESTDIR="/tmp/"
LOCALDIR="/var/www"
PROCESSMESSAGE="Transferring Web files to instance $INSTANCE at $DESTDIR"
echo " * $PROCESSMESSAGE"

for SITEFOLDERPATH in `ls -1d $LOCALDIR/*`;do
  echo " * Transferring $SITEFOLDERPATH to $INSTANCE:$DESTDIR"
  gcloud compute scp --recurse "$SITEFOLDERPATH" $INSTANCE:$DESTDIR
done

echo " * Done $PROCESSMESSAGE"

exit 0
