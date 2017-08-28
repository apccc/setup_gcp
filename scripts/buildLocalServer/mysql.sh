#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo "* Setting up MySQL Server for $COMPANY_NAME on $HOSTNAME"
sudo apt-get install -yqq mysql-server
sudo mysqladmin -u root password $MYSQL_ROOT_PW
echo "* Done setting up MySQL Server"
exit 0
