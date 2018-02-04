#!/bin/bash

#setup maintenance scripts
source ~/setup-config/setup_gcp/core.sh

echo " * Setting up maintenance scripts."
echo "****************************************************"

#install basic cron tasks
echo " * Installing basic cron tasks:"
$AC '4 4 * * 6 ~/setup_gcp/scripts/cron/updateSystem.sh > ~/cron.updateSystem.log 2>&1'
$AC '2 2 * * * ~/setup_gcp/scripts/cron/backupSystem.sh > ~/cron.backupSystem.log 2>&1'


#record vm instance
if [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleComputeEngine_VMInstances WHERE name='$(hostname)'" | tail -n +2 | wc -l` -lt 1 ]];then
  echo " * Creating VM instance entry in ${SYSTEM_DATABASE} for $(hostname)"
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_VMInstances` (`name`,`zone`,`region`,`machine_type`,`ip`,`active`) '
  X=$X'VALUES ("'"$(hostname)"'","'"$(~/setup_gcp/settings/get/gcloud/zone.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/region.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/machine-type.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/ip.sh)"'","T");'
  $MY "$X"
fi


#create home bucket for backup
HOMEBNAME="zstore_${STORAGE_IDENTIFIER}_home"
if [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleCloudStorage_buckets WHERE name='$HOMEBNAME'" | tail -n +2 | wc -l` -lt 1 ]];then
  BNAME=$HOMEBNAME
  BCLASS="DURABLE_REDUCED_AVAILABILITY"
  BLOCATION="US"
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` (`name`,`storageClass`,`bucketLocation`) '
  X=$X'VALUES ("'"$BNAME"'","'"$BCLASS"'","'"$BLOCATION"'");'
  $MY "$X"
  gsutil mb -p "$(~/setup_gcp/settings/get/gcloud/project-id.sh)" -c "$BCLASS" -l "$BLOCATION" "gs://${BNAME}/"
fi


#create backup schedule for home path
HOMEBID=`$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleCloudStorage_buckets WHERE name='$HOMEBNAME' LIMIT 1" | tail -n +2`
if [[ $HOMEBID -gt 0 ]] && [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleCloudStorage_backupSchedule WHERE server='$(hostname)' AND path='/home'" | tail -n +2 | wc -l` -lt 1 ]];then
  echo " * Creating backup schedule for /home directory on $(hostname) to $HOMEBNAME (${HOMEBID})"
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` (`server`,`path`,`bucket_id`,`nextRun`) '
  X=$X'VALUES ("'"$(hostname)"'","/home","$HOMEBID",NOW());'
  echo
  echo "Running: $X"
  $MY "$X"
fi

exit 0
