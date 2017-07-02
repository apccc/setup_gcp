#!/bin/bash

#check for an existing process

#use the pid of current process to check
PID=$1
if [ -z "$PID" ];then
  echo "PID Not Set!"
  exit 0
fi

#get the max iterations allowed
MAX=$2

#get the process name from the current process pid
PNAME=`ps -p $PID | grep $PID | sed 's/ \+/ /g' | sed 's/^ //g' | cut -d' ' -f4`
if [ -z "$PNAME" ];then
  echo "Process Name Not Found!"
  exit 0
fi

#see if the pid file exists
PFILE="/tmp/$PNAME.pid"
if [ -f "$PFILE" ];then
  #get the info from the pid file
  PFILECONTENTS=`cat $PFILE`
  OLDPID=`echo "$PFILECONTENTS" | cut -d' ' -f1`
  OLDPIDCOUNT=`echo "$PFILECONTENTS" | cut -d' ' -f2`
  #See if the process is indeed still running, if not then delete the file
  if [ `ps -p "$OLDPID" | grep -c "$OLDPID"` -lt 1 ];then
    rm $PFILE
  #see if we have reached our max iterations, if so then kill the process, delete the file, and output a 0 for no exi$
  elif [ ! -z "$MAX" ] && [ $OLDPIDCOUNT -gt $MAX ];then
    kill -9 $OLDPID
    rm $PFILE
  else
  #the process is running, and it has not reached the max iterations; update the count of the pid, output 1 and exit
    echo "$OLDPID $(($OLDPIDCOUNT + 1))" > $PFILE
    echo "1"
    exit 0
  fi
fi

#no previous process running now; let the caller know, and store this process in the pid file
echo "0"
echo "$PID 1" > $PFILE
exit 0
