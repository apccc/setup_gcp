#!/bin/bash

#backup SSL Certs

if [ `sudo ls -l /root/ssl/ 2> /dev/null | wc -l` -lt 1 ];then
  echo "No files found to backup!"
  exit 1
fi

SSLBACKUPPATH=~/ssl_backup

if [ ! -d $SSLBACKUPPATH ];then
  echo " * Creating directory $SSLBACKUPPATH"
  mkdir $SSLBACKUPPATH
fi

#sync the data
sudo rsync -a /root/ssl/ ${SSLBACKUPPATH}/

#set the user
U=`whoami`
G=`id -g`
sudo chown -R $U:$G ${SSLBACKUPPATH}

#set the permissions
sudo chmod -R 600 ${SSLBACKUPPATH}

exit 0
