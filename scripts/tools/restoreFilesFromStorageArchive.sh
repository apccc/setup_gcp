#!/bin/bash

SOURCE_ARCHIVE="$1"
if [ -z "$SOURCE_ARCHIVE" ];then
 echo "Source archive not set!"
 exit 0
fi
DESTINATION_DIRECTORY="$2"
if [ -z "$DESTINATION_DIRECTORY" ];then
 echo "Destination directory not set!"
 exit 0
fi
if [ ! -d "$DESTINATION_DIRECTORY" ];then
 echo "Destination directory does not exist!"
 exit 0
fi

echo "Restoring Files From Storage Archive";
echo "Source: $SOURCE_ARCHIVE"
echo "Destination: $DESTINATION_DIRECTORY"

cd "$DESTINATION_DIRECTORY"
echo "Copying File..."
gsutil cp "gs://${SOURCE_ARCHIVE}" .
DESTINATION_FILE=`basename "${SOURCE_ARCHIVE}"`
DESTINATION_FILE_PATH="${DESTINATION_DIRECTORY}/${DESTINATION_FILE}"
if [ ! -f "${DESTINATION_FILE_PATH}" ];then
 echo "File did not transfer!"
 exit 0
fi
echo "Extracting file..."
tar -xzf "${DESTINATION_FILE}"

echo "Done Restoring Files From Storage Archive";

exit 0
