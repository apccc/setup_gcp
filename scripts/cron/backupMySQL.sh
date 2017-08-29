#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

MUSER='root'
MPASS=${MYSQL_ROOT_PW}
MHOST='localhost'
#path to mysqladmin
MADMIN="$(which mysqladmin)"
#BASEBACKUP DIRECTORY
BASEDIRECTORY="/home/${USER_NAME}"
#DAY OF THE WEEK
DAYOFTHEWEEK=`date '+%u'`
#MONTHLY
MONTH=`date '+%Y%m'`
#YEARLY
YEAR=`date '+%Y'`

echo " * BACKING UP MySQL : "$MHOST

MYSQLREPLY="$($MADMIN -h $MHOST -u $MUSER -p$MPASS ping)"
MYSQLCORRECTREPLY="mysqld is alive"

echo " * MySQL Says $MYSQLREPLY"

if [ "$MYSQLREPLY" != "$MYSQLCORRECTREPLY" ]; then
  echo " * Error: MySQL Server is not running/responding to ping request"
  echo " * MySQL is Down"
else
  echo " * MySQL Server $MHOST up"
  BACKUPFOLDER="$BASEDIRECTORY/mysql_backup/$MHOST"
  if [ ! -d "$BACKUPFOLDER" ]; then
    echo " * FOLDER: $BACKUPFOLDER DOES NOT EXIST"
    mkdir -p $BACKUPFOLDER
  fi
  YEARLYBACKUPPATH="$BACKUPFOLDER/dbdump.year.$YEAR.sql.gz"
#       MONTHLYBACKUPPATH="$BACKUPFOLDER/dbdump.month.$MONTH.sql.gz"
  DAILYBACKUPPATH="$BACKUPFOLDER/dbdump.day.$DAYOFTHEWEEK.sql.gz"

#  echo "STOPPING SLAVE"
#  mysql -u $MUSER -h $MHOST -p$MPASS -e 'STOP SLAVE' 2>/dev/null

  DATABASES=`mysql -N -u $MUSER -h $MHOST -p$MPASS -e 'SHOW DATABASES' | egrep -v 'information_schema|performance_schema'`
  if [ ! -f "${YEARLYBACKUPPATH}" ];then
    BACKUPFILE="${YEARLYBACKUPPATH}"
#       elif [ ! -f "${MONTHLYBACKUPPATH}" ];then
#                BACKUPFILE="${MONTHLYBACKUPPATH}"
  else
    BACKUPFILE="${DAILYBACKUPPATH}"
  fi
  echo " * BACKING UP TO: $BACKUPFILE"
  #removed --master-data as there's no master slave replication right now
  (mysql -u $MUSER -h $MHOST -p$MPASS -e 'SHOW SLAVE STATUS' | sed 's/^/-- /' && echo "$DATABASES" | xargs mysqldump --default-character-set=utf8mb4 -u $MUSER -h $MHOST -p$MPASS --databases) | gzip -9 > "$BACKUPFILE"

#        echo "STARTING SLAVE"
#        mysql -u $MUSER -h $MHOST -p$MPASS -e 'START SLAVE' 2>/dev/null
fi

exit 0
