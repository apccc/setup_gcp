#!/bin/bash

#one use key access to cache using expires logic and data entry

if [ -z "$1" ];then
  echo " * Error: cacheCheckout - No key provided!"
  exit 1
fi
KEY=`echo "$1" | egrep -oe '[a-zA-Z0-9_]*' | head -n 1`
if [ -z "$KEY" ];then
  echo " * Error: cacheCheckout - Bad key provided!"
  exit 1
fi

source ~/setup-config/setup_gcp/core.sh

#only a key provided, try to output the data
if [ -z "$2" ];then
  $MY "SELECT data FROM ${SYSTEM_DATABASE}.cache WHERE k='"$KEY"' AND expires > NOW() LIMIT 1" | tail -n +2 | base64 -d
  exit 0
fi

#if two variables given, we must have a third
if [ ! -z "$2" ] && [ -z "$3" ];then
  echo " * Error: cacheCheckout - No data provided!"
  exit 1
fi

#filter days variable
DAYS=`echo "$2" | egrep -oe '[0-9]*' | head -n 1`

#help cleanup
$MY "DELETE FROM ${SYSTEM_DATABASE}.cache WHERE expires <= NOW() LIMIT 1000"

#fail if there is an existing entry
if [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.cache WHERE k='"$KEY"' AND expires > NOW() LIMIT 1" | wc -l` -gt 0 ]];then
  echo 'fail'
  exit 1
fi

#fail if we received an error
if [ `$MY "INSERT INTO ${SYSTEM_DATABASE}.cache (k,expires,data) VALUES('"$KEY"',ADDDATE(NOW(), INTERVAL "$DAYS" DAY),'"$(echo "$3" | base64)"')" 2>&1 | grep ERROR | wc -l` -gt 0 ];then
  echo 'fail'
  exit 1
fi

#all is well
echo 'ok'
exit 0
