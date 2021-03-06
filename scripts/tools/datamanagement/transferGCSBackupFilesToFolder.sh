#!/bin/bash
#Grab Google Cloud Storage backup files and set them up in a folder.

#e.g.:
#scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh zstore_gcvma_datasciencecentral_com_home/7.home.abrahamchaffin.tar.gz /home/

if [ -z "$1" ];then
  echo "Error: GCS source object path not provided."
  exit 1
fi
GCSSOURCEPATH="$1"

if [ ! -z "$2" ];then
  DESTINATIONFOLDER="$2"
else
  DESTINATIONFOLDER="$PWD"
fi

if [ ! -d "$DESTINATIONFOLDER" ];then
  mkdir "$DESTINATIONFOLDER";
fi
TS=`date +%s`
TMPFILEFOLDER="/tmp/setupgcstrans$TS"

#make tmp folder
if [ ! -d "$TMPFILEFOLDER" ];then
  echo "Making $TMPFILEFOLDER"
  mkdir "$TMPFILEFOLDER"
fi

#download to tmp folder
echo "Downloading ${GCSSOURCEPATH} to ${TMPFILEFOLDER}/";
gsutil cp gs://${GCSSOURCEPATH} "${TMPFILEFOLDER}/"

#locate file from tmp folder
DOWNLOADEDTMPFILE=`ls -1 "${TMPFILEFOLDER}/"`
echo "Downloaded file: ${DOWNLOADEDTMPFILE}"

FILEEXT="${DOWNLOADEDTMPFILE##*.}"
echo "File extension found: $FILEEXT"

#extract file from tmp folder
if [ "$FILEEXT" == 'gz' ];then
  echo "Extracting file ${TMPFILEFOLDER}/${DOWNLOADEDTMPFILE} to ${DESTINATIONFOLDER}"
  tar xf "${TMPFILEFOLDER}/${DOWNLOADEDTMPFILE}" -C "${DESTINATIONFOLDER}"
fi

#clean up
if [ -d "$TMPFILEFOLDER" ];then
  echo "Cleaning up..."
  echo "Removing $TMPFILEFOLDER"
  rm -rf "$TMPFILEFOLDER"
fi

exit 0
