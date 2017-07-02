#!/bin/bash
echo "Updating APT:"
~/setup/scripts/tools/systemUpgrades/aptUpdate.sh
echo ""
echo "---------------------"
echo "Upgrading The System:"
#apt-get -y upgrade
~/setup/scripts/tools/systemUpgrades/doUpgradeCatch.exp
exit 0