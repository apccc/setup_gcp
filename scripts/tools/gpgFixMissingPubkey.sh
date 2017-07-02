#!/bin/bash

KEY=`echo "$1" | egrep -o -e '[0-9A-Z]+'`
if [ -z "$KEY" ];then
  echo "Missing Key";
  exit 1
fi

KEYSERVER="pgp.mit.edu"

echo "Trying to Fix Missing Key: $KEY"
echo "Setting up debian-keyring:"
sudo apt-get install -y --force-yes debian-keyring
echo ""
echo "Grabbing Key:"
sudo gpg --keyserver "hkp://${KEYSERVER}:80" --recv-keys "$KEY"
echo ""
echo "Adding Key:"
sudo gpg --armor --export "$KEY" | apt-key add -
exit 0