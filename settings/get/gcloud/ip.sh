#!/bin/bash
gcloud compute instances describe `hostname` --zone=$(~/setup_gcp/settings/get/gcloud/zone.sh) | grep natIP | egrep -oe '[0-9.]+$'
exit 0
