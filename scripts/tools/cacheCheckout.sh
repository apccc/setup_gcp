#!/bin/bash

#one use key access to cache using expires logic and data entry

if [ -z "$1" ];then
  echo " * Error: cacheCheckout - No key provided!"
  exit 1
fi
if [ -z "$2" ];then
  echo " * Error: cacheCheckout - No days provided!"
  exit 1
fi
if [ -z "$3" ];then
  echo " * Error: cacheCheckout - No data provided!"
  exit 1
fi

KEY=`echo "$1" | egrep -oe '[a-zA-Z0-9_]*' | head -n 1`
DAYS=`echo "$2" | egrep -oe '[0-9]*' | head -n 1`

source ~/setup-config/setup_gcp/core.sh

#fail if there is an existing entry, other wise store and output okay
if [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.cache WHERE k='"$KEY"' AND expires > NOW() LIMIT 1" | wc -l` -gt 0 ]];then
  echo 'fail'
else
  $MY "INSERT INTO ${SYSTEM_DATABASE}.cache (k,expires,data) VALUES('"$KEY"',ADDDATE(NOW(), INTERVAL "$DAYS" DAY),'"$(echo $3 | base64)"')"
  echo 'ok'
fi

#help cleanup
$MY "DELETE FROM ${SYSTEM_DATABASE}.cache WHERE expires <= NOW() LIMIT 10000"

exit 0
