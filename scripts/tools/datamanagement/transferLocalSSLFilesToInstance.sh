#!/bin/bash

#Transfer local SSL files in the /root/ssl directory to a remote instance

if [ -z "$1" ];then
  echo "Error: Remote instance name not provided."
  exit 1
fi
INSTANCE=`echo "$1" | egrep -oe '[a-zA-Z0-9_.-]*' | head -n 1`

echo "Transferring SSL files to instance $INSTANCE"

exit 0
mkdir /tmp/ssl
sudo rsync -av /root/ssl/ /tmp/ssl/
gcloud compute scp --recurse /tmp/ssl $INSTANCE:/tmp/
rm -r /tmp/ssl

exit 0
