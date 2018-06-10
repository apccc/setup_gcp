#!/bin/bash

#Transfer local SSL files in the /root/ssl directory to a remote instance

if [ -z "$1" ];then
  echo "Error: Remote instance name not provided."
  exit 1
fi
INSTANCE=`echo "$1" | egrep -oe '[a-zA-Z0-9_.-]*' | head -n 1`
DESTDIR="/tmp/"
LOCALDIR="/tmp/ssl"
PROCESSMESSAGE="Transferring SSL files to instance $INSTANCE at $DESTDIR"
echo "$PROCESSMESSAGE"

mkdir "$LOCALDIR"
sudo rsync -av /root/ssl/ "${LOCALDIR}/"
gcloud compute scp --recurse "$LOCALDIR" $INSTANCE:$DESTDIR
sudo rm -r "$LOCALDIR"

echo "Done $PROCESSMESSAGE"

exit 0
