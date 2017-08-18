#!/bin/bash
#make sure this is not already running
if [ `~/setup/scripts/cron/tools/checkForExistingProcess.sh "$$" 10` == '1' ];then
  echo "Process is Running"
  exit 1
fi

source ~/setup/settings/core.sh

~/setup/scripts/cron/backupMySQL.sh 

GSUTIL=`which gsutil`

if [ ! -f $GSUTIL ];then
  echo " * $GSUTIL does not exist"
  exit 1
fi

BACKUP_IDENTIFIER_STAMP="$(date +%u)"

echo " * Running Google Cloud Storage Scheduled Backups for $HOSTNAME"

#see if there are any scheduled backups
for SCHEDULEDBACKUPS_ID in $($MY 'SELECT id FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `nextRun`<=NOW() AND `server`="'$HOSTNAME'"' | tail -n +2);do
  echo " * Backing up at - $(date)"
  echo " * Found scheduled backup id: $SCHEDULEDBACKUPS_ID"
  SCHEDULEDBACKUPS_LASTRUN=$($MY 'SELECT `lastRun` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `id`="'$SCHEDULEDBACKUPS_ID'"' | tail -n 1)
  echo " * Last run on $SCHEDULEDBACKUPS_LASTRUN"
  SCHEDULEDBACKUPS_PATH=$($MY 'SELECT `path` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `id`="'$SCHEDULEDBACKUPS_ID'"' | tail -n 1)
  echo " * For path at $SCHEDULEDBACKUPS_PATH"
  SCHEDULEDBACKUPS_RUNFREQUENCYDAYS=$($MY 'SELECT `runFrequencyDays` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `id`="'$SCHEDULEDBACKUPS_ID'"' | tail -n 1)
  echo " * Runs every $SCHEDULEDBACKUPS_RUNFREQUENCYDAYS Days"
  SCHEDULEDBACKUPS_BUCKETID=$($MY 'SELECT `bucket_id` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `id`="'$SCHEDULEDBACKUPS_ID'"' | tail -n 1)
  echo " * Bucket Id $SCHEDULEDBACKUPS_BUCKETID"
  SCHEDULEDBACKUPS_STORAGECLASS=$($MY 'SELECT `storageClass` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` WHERE `id`="'$SCHEDULEDBACKUPS_BUCKETID'"' | tail -n 1)
  echo " * Storage Class $SCHEDULEDBACKUPS_STORAGECLASS"
  SCHEDULEDBACKUPS_BUCKETLOCATION=$($MY 'SELECT `bucketLocation` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` WHERE `id`="'$SCHEDULEDBACKUPS_BUCKETID'"' | tail -n 1)
  echo " * Bucket Location $SCHEDULEDBACKUPS_BUCKETLOCATION"
  GCSBUCKET=$($MY 'SELECT `name` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` WHERE `id`="'$SCHEDULEDBACKUPS_BUCKETID'"' | tail -n 1)
  echo " * GCS Bucket: $GCSBUCKET"
  PATHSTOOMITCOMMAND=""
  for PATHTOOMIT in $(echo -e $($MY 'SELECT `pathsToOmit` FROM `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` WHERE `id`="'$SCHEDULEDBACKUPS_ID'"' | tail -n +2));do
    echo " * PATH TO OMIT: $PATHTOOMIT"
    PATHTOOMIT=$(echo $PATHTOOMIT | sed 's/\s//g')
    PATHSTOOMITCOMMAND="$PATHSTOOMITCOMMAND -not -iwholename '"$PATHTOOMIT"'"'/\*'
  done
  echo " * PATHS TO OMIT COMMAND: $PATHSTOOMITCOMMAND"

  echo ""
  echo " * Updating the scheduler and setting it to run again in the future."
  $MY 'UPDATE `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` SET lastRun=NOW(), nextRun=DATE_SUB(DATE_ADD(NOW(),INTERVAL '"$SCHEDULEDBACKUPS_RUNFREQUENCYDAYS"' DAY),INTERVAL 1 HOUR) WHERE `id`="'$SCHEDULEDBACKUPS_ID'"'

  if [ ! -d "$SCHEDULEDBACKUPS_PATH" ];then
    echo " * Path $SCHEDULEDBACKUPS_PATH does not exist! skipping..."
    continue
  fi

  if [ "$SCHEDULEDBACKUPS_LASTRUN" == "0000-00-00 00:00:00" ];then
    echo " * Backup not run - will try to create the bucket:"
    $GSUTIL mb -p "$(~/setup/settings/get/gcloud/project-id.sh)" -c "$SCHEDULEDBACKUPS_STORAGECLASS" -l "$SCHEDULEDBACKUPS_BUCKETLOCATION" "gs://$GCSBUCKET"
  fi
  echo "Source: $SCHEDULEDBACKUPS_PATH"
  echo "Destination: gs://$GCSBUCKET"
  SCHEDULEDBACKUPS_COMPRESSED_FILE_NAME="${BACKUP_IDENTIFIER_STAMP}"`echo "$SCHEDULEDBACKUPS_PATH" | sed 's/\//./g' | sed 's/[^a-zA-Z0-9_.-]//g'`".tar.gz"
  SCHEDULEDBACKUPS_COMPRESSED_FILE_DIR="/tmp/backupsystem/$GCSBUCKET"
  if [ ! -d "$SCHEDULEDBACKUPS_COMPRESSED_FILE_DIR" ];then
    mkdir -p $SCHEDULEDBACKUPS_COMPRESSED_FILE_DIR
  fi
  SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH="${SCHEDULEDBACKUPS_COMPRESSED_FILE_DIR}/${SCHEDULEDBACKUPS_COMPRESSED_FILE_NAME}"
  ~/setup/scripts/tools/datamanagement/compressDirectoryToTarGz.sh "$SCHEDULEDBACKUPS_PATH" "$SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH"
  echo "Copying $SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH to gs://$GCSBUCKET"
  $GSUTIL cp "$SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH" "gs://$GCSBUCKET"
  echo "Removing local file $SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH"
  rm "$SCHEDULEDBACKUPS_COMPRESSED_FILE_PATH"
  echo " * Done with scheduled backup $SCHEDULEDBACKUPS_ID. $(date)"
done

echo " * Done with Scheduled Backups."

exit 0
