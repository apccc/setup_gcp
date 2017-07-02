#!/bin/bash

gcloud compute instances describe `hostname` --zone=$(~/setup/settings/get/gcloud/zone.sh) | grep region | egrep -oe '/regions/[0-9a-zA-Z_.-]+' | egrep -oe '[0-9a-zA-Z_.-]+$'

exit 0
