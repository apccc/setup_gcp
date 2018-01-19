#!/bin/bash

#build a local MySQL slave from master backup file

if [ -z "$1" ];then
  echo "File not set!"
  exit 1
fi

if [ ! -f "$1" ];then
  echo "No file found $1"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh



#turn off external connections while we work

#turn off any existing slave working

#ingest the data

#setup replication information

#start the slave back up



exit 0
