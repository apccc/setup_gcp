#!/bin/bash

#setup maintenance scripts
source ~/setup-config/setup_gcp/core.sh

echo " * Setting up maintenance scripts."
echo "****************************************************"

#install basic cron tasks
echo " * Installing basic cron tasks:"
$AC '4 4 * * 6 ~/setup_gcp/scripts/cron/updateSystem.sh > ~/cron.updateSystem.log 2>&1'
$AC '2 2 * * * ~/setup_gcp/scripts/cron/backupSystem.sh > ~/cron.backupSystem.log 2>&1'

exit 0
