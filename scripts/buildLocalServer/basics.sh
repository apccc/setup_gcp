#!/bin/bash
source ~/setup-config/setup_gcp/core.sh

echo "* Setting Up Basic System Settings for $COMPANY_NAME"
echo "****************************************************"

echo "* Updating apt"
sudo apt-get update -qq

sudo apt-get install -yqq git
if [ ! -d "~/github_utils" ];then
  cd ~
  git clone https://github.com/apccc/github_utils.git
fi

echo "* Installing these basic packages:"
cat ~/setup_gcp/settings/basics.packages.txt
echo "* Installing with yes set for all answers:"
sudo apt-get install -yqq --force-yes `cat ~/setup_gcp/settings/basics.packages.txt | tr "\n" " "`

#install basic cron tasks
$AC '4 4 * * 6 ~/setup_gcp/scripts/cron/updateSystem.sh > ~/cron.updateSystem.log 2>&1'
$AC '2 2 * * * ~/setup_gcp/scripts/cron/backupSystem.sh > ~/cron.backupSystem.log 2>&1'
exit 0
