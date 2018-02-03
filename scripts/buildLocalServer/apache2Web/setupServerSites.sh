#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Setting up Server Sites"

VMID=`$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleComputeEngine_VMInstances WHERE name='$(hostname)' LIMIT 1" | tail -n 1`
if [ ! -z "$VMID" ];then
  echo " * Looking for sites for VM with id $VMID"
  for SITEID in $($MY "SELECT site_id FROM ${SYSTEM_DATABASE}.sites_vms WHERE vm_id='${VMID}'" | tail -n +2);do
    SERVERWEBSITEID=`$MY "SELECT id FROM ${SYSTEM_DATABASE}.sites WHERE subdomain !='${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}' AND active='T' AND id='${SITEID}'" | tail -n 1`
    if [ ! -z "$SERVERWEBSITEID" ];then
      SUBDOMAIN=`$MY "SELECT subdomain FROM ${SYSTEM_DATABASE}.sites WHERE id='${SERVERWEBSITEID}' LIMIT 1" | tail -n 1`
      ~/setup_gcp/scripts/buildLocalServer/apache2Web/setupSite.sh "${SUBDOMAIN}"
    fi
  done
fi
exit 0
