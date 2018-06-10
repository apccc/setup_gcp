#!/bin/bash

#Transfer Local Files To Instance

if [ -z "$1" ];then
  echo "Error: Remote instance name not provided."
  exit 1
fi
INSTANCE=`echo "$1" | egrep -oe '[a-zA-Z0-9_.-]*' | head -n 1`

LOCALDIR=`dirname $0`

$LOCALDIR/transferLocalSSLFilesToInstance.sh "$INSTANCE"
$LOCALDIR/transferLocalDatabaseBackupToInstance.sh "$INSTANCE"
$LOCALDIR/transferLocalWebFilesToInstance.sh "$INSTANCE"

exit 0
