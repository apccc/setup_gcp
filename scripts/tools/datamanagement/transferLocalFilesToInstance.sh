#!/bin/bash

#Transfer Local Files To Instance

LOCALDIR=`dirname $0`

$LOCALDIR/transferLocalSSLFilesToInstance.sh
$LOCALDIR/transferLocalDatabaseBackupToInstance.sh
$LOCALDIR/transferLocalWebFilesToInstance.sh

exit 0
