#!/bin/bash

#Transfer local Database backup file to a remote instance

if [ -z "$1" ];then
  echo "Error: Remote instance name not provided."
  exit 1
fi
INSTANCE=`echo "$1" | egrep -oe '[a-zA-Z0-9_.-]*' | head -n 1`

HOSTDIR=`ls -1 ~/mysql_backup/ | head -n 1`

if [ -z "$HOSTDIR" ];then
  echo "transferDatabaseBackupToInstance: No Host Dir Found!"
fi

RECENTFILE=`ls -lt ~/mysql_backup/$HOSTDIR/ | egrep -oe 'dbdump.[a-zA-Z0-9.]*.sql.gz' | head -n 1`
LOCALBACKUPFILEPATH=~/mysql_backup/$HOSTDIR/$RECENTFILE

if [ ! -f "$LOCALBACKUPFILEPATH" ];then
  echo "transferDatabaseBackupToInstance: No File Found!"
fi

DESTDIR="/tmp/"
PROCESSMESSAGE="Transferring database backup $LOCALBACKUPFILEPATH to instance $INSTANCE at $DESTDIR"
echo "$PROCESSMESSAGE"

gcloud compute scp "$LOCALBACKUPFILEPATH" $INSTANCE:$DESTDIR

echo "Done $PROCESSMESSAGE"

exit 0
