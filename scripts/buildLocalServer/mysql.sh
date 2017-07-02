#!/bin/bash

source ~/setup/settings/core.sh

echo "* Setting up MySQL Server for $COMPANY_NAME on $HOSTNAME"

~/setup/scripts/buildLocalServer/mysql/installMySQLServer.exp

echo "* Done setting up MySQL Server"

exit 0
