#!/bin/bash

#Setup the system
if [ ! -d ~/setup-config/setup_gcp ];then
 echo "Creating ~/setup-config/setup_gcp"
 mkdir -p ~/setup-config/setup_gcp;
fi
if [ ! -f ~/setup-config/setup_gcp/core.sh ];then
 echo "Setting up core settings file."
 cp ~/setup_gcp/settings/core.template.sh ~/setup-config/setup_gcp/core.sh
fi


exit 0
