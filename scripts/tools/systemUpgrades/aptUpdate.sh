#!/bin/bash

#do a high quality apt-get update

echo "Updating APT"

if [ `sudo apt-get update 2>&1 | grep NO_PUBKEY | wc -l` -lt 1 ];then
  echo "APT Updated"
  exit 0
fi

TMP=/tmp/pubkeys.tmp

#we need some keys
sudo apt-get update 2>&1 | grep NO_PUBKEY | egrep -o -e '[0-9A-Z]+$' > $TMP
while read KEY;do
  ~/setup/scripts/tools/gpgFixMissingPubkey.sh "$KEY"
done < $TMP
rm $TMP

sudo apt-get update

exit 0