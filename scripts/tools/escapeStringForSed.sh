#!/bin/bash

STRING=$1
if [ -z "$STRING" ];then
        echo "STRING variable not set."
        exit
fi
echo "$STRING" | sed 's/[^-\t\?\x27\x28\x29\x3C\x3EA-Za-z0-9_{]/\\&/g' | sed ':a;N;$!ba;s/\n/\\n/g' | sed 's/\t/\\t/g' | sed 's/\?/\\x3F/g' | sed 's/^\\t/\\'$'\t''/'

exit 0
