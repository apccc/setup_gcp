#!/bin/bash

#global vars
COMPANY_NAME='<<COMPANYNAME>>'
COMPANY_DOMAIN='<<COMPANYDOMAIN>>'
COMPANY_ADMIN_SUBDOMAIN='<<COMPANYADMINSUBDOMAIN>>'
COMPANY_SYSADMIN_EMAIL='<<COMPANYSYSADMINEMAIL>>'
SYSADMIN_INIT_PASS='<<SYSADMININITPASSWORD>>'

#mid level vars
MYSQL_ROOT_PW='<<MYSQLROOTPASS>>'
MYSQL_1_HOST='localhost'
SYSTEM_DATABASE='setup_system'
MYSQL_WEB_USER='webadmin'
MYSQL_WEB_USER_PASS='<<MYSQLWEBUSERPASS>>'
PHPMYADMIN_FOLDER='<<PHPMYADMINFOLDER>>-phpmyadmin'
WHITELISTEDIPS=''
MAILGUN_SMTP_USERNAME='<<MAILGUNSMTPUSERNAME>>';
MAILGUN_SMTP_PASSWORD='<<MAILGUNSMTPPASSWORD>>';
SITE_TOS_URL="https://${COMPANY_DOMAIN}/tos"
SITE_PRIVACY_URL="https://${COMPANY_DOMAIN}/privacy"
RECAPTCHA_SITE_KEY='<<RECAPTCHASSITEKEY>>'
RECAPTCHA_SECRET_KEY='<<RECAPTCHASECRETKEY>>'

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
