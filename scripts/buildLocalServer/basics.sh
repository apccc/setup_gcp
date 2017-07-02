#!/bin/bash
source ~/setup/settings/core.sh

echo "* Setting Up Basic System Settings for $COMPANY_NAME"
echo "****************************************************"

echo "* Updating apt"
sudo apt-get update

echo "* Installing these basic packages:"
cat ~/setup/settings/basics.packages.txt
echo "* Installing with yes set for all answers:"
sudo apt-get install -y --force-yes `cat ~/setup/settings/basics.packages.txt | tr "\n" " "`

exit 0
