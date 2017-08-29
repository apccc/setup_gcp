#!/bin/bash
echo "Updating APT:"
~/setup_gcp/scripts/tools/systemUpgrades/aptUpdate.sh
echo ""
echo "---------------------"
echo "Upgrading The System:"
#apt-get -y upgrade
~/setup_gcp/scripts/tools/systemUpgrades/doUpgradeCatch.exp
exit 0
