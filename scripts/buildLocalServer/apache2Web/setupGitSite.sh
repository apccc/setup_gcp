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

#check if target exists already
D="/var/www/${GITREPO}"
if [[ `ls -l "$D" 2>/dev/null | wc -l` -gt 0 ]];then
  echo " * $D already exists! Checking for updates..."
  cd "$D"
  ~/github_utils/repo_pull.sh
else
  echo " * $D not found! Trying to clone..."
  HOLDERD=~/setup-config/setup_gcp/git_sites
  if [ ! -d $HOLDERD ];then
    echo " * Making directory $HOLDERD"
    mkdir $HOLDERD
  fi
  cd $HOLDERD/
  if [ -d $HOLDERD/$GITREPO ];then
    echo " * Directory $HOLDERD/$GITREPO exists! Checking for updates..."
    cd $HOLDERD/$GITREPO
    ~/github_utils/repo_pull.sh
  else
    echo " * Cloning "${GITORG}/${GITREPO}" to ${HOLDERD}/${GITREPO}..."
    ~/github_utils/repo_clone.sh "$GITORG" "$GITREPO"
  fi
  H=`echo ~`
  echo " * Creating site file link at /var/www/$GITREPO"
  sudo ln -s $H/setup-config/setup_gcp/git_sites/$GITREPO/ /var/www/
fi

exit 0
