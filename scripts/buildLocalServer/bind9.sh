#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo "* Building Bind9 DNS Server"
echo "****************************"

sudo apt-get install -y bind9

exit 0
