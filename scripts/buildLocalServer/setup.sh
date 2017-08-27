#!/bin/bash

#Setup the system
if [ ! -d ~/setup-config/setup_gcp ];then
 echo "Creating ~/setup-config/setup_gcp"
 mkdir -p ~/setup-config/setup_gcp;
fi
#Setup template file
F=~/setup-config/setup_gcp/core.sh
if [ ! -f ~/setup-config/setup_gcp/core.sh ];then
 echo "Setting up core settings file."
 cp ~/setup_gcp/settings/core.template.sh $F
fi
#Setup company name
if [ `grep '<<COMPANYNAME>>' "$F" | wc -l` -gt 0 ];then
  echo -n "Enter the Company Name: "
  read COMPANYNAME
  sed -i -e "s/<<COMPANYNAME>>/${COMPANYNAME}/g" "$F"
fi

exit 0
