#!/bin/bash

if [ -z "$1" ];then
  echo "File not set!"
  exit 1
fi

if [ ! -f "$1" ];then
  echo "No file found $1"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh
mysqlPass=`~/setup_gcp/settings/get/mysql_r_pw.sh`

INCOMINGFILE="$1"

BASEFILENAME=`basename ${INCOMINGFILE}`
echo "File found: $BASEFILENAME"

FILEEXT="${BASEFILENAME##*.}"
echo "File extension found: $FILEEXT"

echo "Ingesting File to MySQL: $INCOMINGFILE"
if [ "$FILEEXT" == 'gz' ];then
  zcat "$INCOMINGFILE" | mysql -u root -p$mysqlPass --host "$MYSQL_1_HOST"
else
  mysql -u root -p$mysqlPass --host "$MYSQL_1_HOST" < "${INCOMINGFILE}"
fi

#cleanup tmp folder
if [ -d "$TMPFILEFOLDER" ];then
  echo "Removing $TMPFILEFOLDER"
  rm -rf "$TMPFILEFOLDER"
fi

exit 0
