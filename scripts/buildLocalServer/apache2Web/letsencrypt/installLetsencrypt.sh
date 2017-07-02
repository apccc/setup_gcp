#!/bin/bash

D="/root/letsencrypt"
if [[ `sudo ls -1 "$D" | wc -l` -lt 1 ]];then
  echo " * Installing letsencrypt in $D"
  sudo mkdir "$D"
  sudo apt-get update
  sudo apt-get -y install git
  sudo git clone https://github.com/letsencrypt/letsencrypt "$D"
  echo " * Done installing letsencrypt"
else
  echo " * Letsencrypt is installed in $D"
fi

exit 0
