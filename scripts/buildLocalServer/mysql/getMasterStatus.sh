#!/bin/bash

#Get the master status of the server

source ~/setup-config/setup_gcp/core.sh

mysql -u root -p"$MYSQL_ROOT_PW" -e "SHOW MASTER STATUS;" | grep mysql-bin | sed 's/\s\+/ /g' | sed 's/\s$//'

exit 0
