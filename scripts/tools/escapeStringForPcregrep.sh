#!/bin/bash

STRING=$1
if [ -z "$STRING" ];then
        echo "STRING variable not set."
        exit
fi
echo "$STRING" | sed 's/[^-\tA-Za-z0-9_{]/\\&/g' | sed ':a;N;$!ba;s/\n/\\n/g' | sed 's/\t/\\t/g'

exit 0
