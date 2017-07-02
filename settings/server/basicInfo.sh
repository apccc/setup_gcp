#!/bin/bash

SERVER_RAM_MB=`free -m | grep 'Mem' | egrep -o '([0-9]+)' | head -n1`
