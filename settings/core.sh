#!/bin/bash

#global vars
COMPANY_NAME='ACME'
COMPANY_DOMAIN='example.com'
COMPANY_ADMIN_SUBDOMAIN='admin'
COMPANY_SYSADMIN_EMAIL='sysadmin@example.com'
SYSADMIN_INIT_PASS=''

#mid level vars
MYSQL_ROOT_PW=''
MYSQL_1_HOST='localhost'
SYSTEM_DATABASE='setup_system'
MYSQL_WEB_USER='webadmin'
MYSQL_WEB_USER_PASS=''
PHPMYADMIN_FOLDER='za4dz-phpmyadmin'
WHITELISTEDIPS=''
MAILGUN_SMTP_USERNAME='postmaster@mg.example.com';
MAILGUN_SMTP_PASSWORD='32f3e5352';
SITE_TOS_URL='https://example.com/tos'
SITE_PRIVACY_URL='https://example.com/privacy'
RECAPTCHA_SITE_KEY='6Lz'
RECAPTCHA_SECRET_KEY='62'

#dynamic vars
USER_NAME=`cat /etc/passwd | grep 1000 | cut -d':' -f1`
HOSTNAME=`hostname`
STORAGE_IDENTIFIER=`echo "${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}_$(hostname)" | tr '.' '_'`

#commands
FR=~/setup/scripts/tools/findreplace.sh
AP=~/setup/scripts/tools/appendFileOnce.sh
AC=~/setup/scripts/tools/appendCronOnce.sh
IN=~/setup/scripts/tools/inFile.sh
MY=~/setup/scripts/tools/mysql/commandServer1Link.sh
