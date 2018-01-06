#!/bin/bash

#Setup a git site

if [ -z "$1" ];then
  echo "Git repo address not set"
  exit
fi
