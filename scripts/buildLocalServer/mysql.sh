#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo "* Setting up MySQL Server for $COMPANY_NAME on $HOSTNAME"
~/setup_gcp/scripts/buildLocalServer/mysql/installMySQLServer.exp
echo "* Done setting up MySQL Server"
exit 0
