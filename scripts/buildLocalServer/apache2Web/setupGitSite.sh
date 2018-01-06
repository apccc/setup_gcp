#!/bin/bash

#Setup a git site

if [ -z "$1" ];then
  echo "Git repo address not set"
  exit
fi
GITADDRESS="$1"
if [ "${GITADDRESS:0:4}" != "git:" ];then
  echo "Git address protocol incorrect!"
  exit
fi
echo " * Setting up Git site with address $GITADDRESS"
GITADDRESS=${GITADDRESS:4}
GITORG=`echo "$GITADDRESS" | cut -d'/' -f1`
GITREPO=`echo "$GITADDRESS" | cut -d'/' -f2`
if [ -z "$GITORG" ];then
  echo "Git organization not found!"
  exit
fi
if [ -z "$GITREPO" ];then
  echo "Git repo not found!"
  exit
fi


exit 0
