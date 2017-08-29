#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

if [ -z "$MYSQL_ROOT_PW" ];then
 echo "MySQL root password not set!"
 exit 1
fi

echo "* Setting up MySQL Server for $COMPANY_NAME on $HOSTNAME"
sudo apt-get install -yqq mysql-server
sudo mysql-u root -e "DROP USER 'root'@'localhost';CREATE USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PW}';GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' with grant option;FLUSH PRIVILEGES;"
echo "* Done setting up MySQL Server"
exit 0
