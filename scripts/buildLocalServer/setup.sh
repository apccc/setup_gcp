#!/bin/bash

#Setup the system
if [ ! -d ~/setup-config/setup_gcp ];then
 echo " * Creating ~/setup-config/setup_gcp"
 mkdir -p ~/setup-config/setup_gcp;
fi

#Setup template file
F=~/setup-config/setup_gcp/core.sh
if [ ! -f ~/setup-config/setup_gcp/core.sh ];then
 echo " * Setting up core settings file."
 cp ~/setup_gcp/settings/core.template.sh $F
 chmod 600 $F
fi
#Setup company info
if [ `grep '<<COMPANYNAME>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Company Name (e.g. ACME Inc.): "
  read COMPANYNAME
  sed -i -e "s/<<COMPANYNAME>>/${COMPANYNAME}/g" "$F"
fi
if [ `grep '<<COMPANYDOMAIN>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Company Domain (e.g. example.com): "
  read COMPANYDOMAIN
  sed -i -e "s/<<COMPANYDOMAIN>>/${COMPANYDOMAIN}/g" "$F"
fi
if [ `grep '<<COMPANYADMINSUBDOMAIN>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Company Admin Subdomain (e.g. admin): "
  read COMPANYADMINSUBDOMAIN
  sed -i -e "s/<<COMPANYADMINSUBDOMAIN>>/${COMPANYADMINSUBDOMAIN}/g" "$F"
fi
if [ `grep '<<COMPANYSYSADMINEMAIL>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Admin Email (e.g. admin@example.com): "
  read COMPANYSYSADMINEMAIL
  sed -i -e "s/<<COMPANYSYSADMINEMAIL>>/${COMPANYSYSADMINEMAIL}/g" "$F"
fi
if [ `grep '<<SYSADMININITPASSWORD>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Admin Pass (leave blank to auto-generate): "
  read SYSADMININITPASSWORD
  if [ -z "$SYSADMININITPASSWORD" ];then
    SYSADMININITPASSWORD=`tr -cd [:alnum:] < /dev/urandom | head -c 16`
  fi
  sed -i -e "s/<<SYSADMININITPASSWORD>>/${SYSADMININITPASSWORD}/g" "$F"
fi
if [ `grep '<<MYSQL1HOST>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * MySQL Host (leave blank for localhost): "
  read MYSQL1HOST
  if [ -z "$MYSQL1HOST" ];then
    MYSQL1HOST='localhost'
  fi
  sed -i -e "s/<<MYSQL1HOST>>/${MYSQL1HOST}/g" "$F"
fi
if [ `grep '<<MYSQLROOTPASS>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * MySQL Root Pass (leave blank to auto-generate): "
  read MYSQLROOTPASS
  if [ -z "$MYSQLROOTPASS" ];then
    MYSQLROOTPASS=`tr -cd [:alnum:] < /dev/urandom | head -c 16`
  fi
  sed -i -e "s/<<MYSQLROOTPASS>>/${MYSQLROOTPASS}/g" "$F"
fi
if [ `grep '<<MYSQLWEBUSERPASS>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * MySQL Web Pass (leave blank to auto-generate): "
  read MYSQLWEBUSERPASS
  if [ -z "$MYSQLWEBUSERPASS" ];then
    MYSQLWEBUSERPASS=`tr -cd [:alnum:] < /dev/urandom | head -c 16`
  fi
  sed -i -e "s/<<MYSQLWEBUSERPASS>>/${MYSQLWEBUSERPASS}/g" "$F"
fi
if [ `grep '<<MYSQLREPLICPASS>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * MySQL Replication Pass (leave blank to auto-generate): "
  read MYSQLREPLICPASS
  if [ -z "$MYSQLREPLICPASS" ];then
    MYSQLREPLICPASS=`tr -cd [:alnum:] < /dev/urandom | head -c 16`
  fi
  sed -i -e "s/<<MYSQLREPLICPASS>>/${MYSQLREPLICPASS}/g" "$F"
fi
if [ `grep '<<SYSTEMDATABASE>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Setup System Database Name (leave blank for setup_system): "
  read SYSTEMDATABASE
  if [ -z "$SYSTEMDATABASE" ];then
    SYSTEMDATABASE='setup_system'
  fi
  SYSTEMDATABASE=`echo "$SYSTEMDATABASE" | egrep -oe '[a-zA-Z0-9_]*' | head -n 1`
  sed -i -e "s/<<SYSTEMDATABASE>>/${SYSTEMDATABASE}/g" "$F"
fi
if [ `grep '<<PHPMYADMINFOLDER>>' "$F" | wc -l` -gt 0 ];then
  PHPMYADMINFOLDER=`tr -cd [:alnum:] < /dev/urandom | head -c 16`
  sed -i -e "s/<<PHPMYADMINFOLDER>>/${PHPMYADMINFOLDER}/g" "$F"
fi
if [ `grep '<<MAILGUNSMTPUSERNAME>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Enter the Mailgun SMTP User Name (e.g. admin@mg.example.com): "
  read MAILGUNSMTPUSERNAME
  sed -i -e "s/<<MAILGUNSMTPUSERNAME>>/${MAILGUNSMTPUSERNAME}/g" "$F"
fi
if [ `grep '<<MAILGUNSMTPPASSWORD>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Enter the Mailgun SMTP Password: "
  read MAILGUNSMTPPASSWORD
  echo ""
  sed -i -e "s/<<MAILGUNSMTPPASSWORD>>/${MAILGUNSMTPPASSWORD}/g" "$F"
fi
if [ `grep '<<RECAPTCHASSITEKEY>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Enter the ReCaptcha Site Key: "
  read RECAPTCHASSITEKEY
  sed -i -e "s/<<RECAPTCHASSITEKEY>>/${RECAPTCHASSITEKEY}/g" "$F"
fi
if [ `grep '<<RECAPTCHASECRETKEY>>' "$F" | wc -l` -gt 0 ];then
  echo -n " * Enter the ReCaptcha Secret Key: "
  read RECAPTCHASECRETKEY
  echo ""
  sed -i -e "s/<<RECAPTCHASECRETKEY>>/${RECAPTCHASECRETKEY}/g" "$F"
fi
if [ `grep '<<WHITELISTEDIPS>>' "$F" | wc -l` -gt 0 ];then
  ZWHITEIP=`sudo netstat -antp | grep ':22' | grep 'ESTABLISHED' | sed 's/\s\s*/ /g' | cut -d' ' -f5 | cut -d':' -f1 | egrep -oe '[0-9]+.[0-9]+.[0-9]+.[0-9]+' | head -n 1`
  sed -i -e "s/<<WHITELISTEDIPS>>/${ZWHITEIP}/g" "$F"
  if [ ! -z "$ZWHITEIP" ];then
    echo " *** Whitelisting connected IP $ZWHITEIP ***"
  fi
fi

#Authenticate
gcloud auth login

#Do basic install
~/setup_gcp/scripts/buildLocalServer/basics.sh

#Include variables from config file
source "$F"


#Do the builds

#Mailgun & Postfix
~/setup_gcp/scripts/buildLocalServer/mailgunPostfix.sh

#Database
echo " * Install Local Database? (y/n)"
read X
if [[ "$X" == "y" ]];then
  ~/setup_gcp/scripts/buildLocalServer/mysql.sh
fi

#Web Server
echo " * Install Local Web Server? (y/n)"
read X
if [[ "$X" == "y" ]];then
  ~/setup_gcp/scripts/buildLocalServer/apache2Web.sh
  #Output next steps
  COADURL="https://${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}/"
  echo " * Admin URL: ${COADURL}admin/"
  #PHPMyAdmin
  echo " * Install Local PHPMyAdmin? (y/n)"
  read X
  if [[ "$X" == "y" ]];then
    ~/setup_gcp/scripts/buildLocalServer/phpMyAdmin.sh
    echo " * PHPMyAdmin URL: ${COADURL}${PHPMYADMIN_FOLDER}"
  fi
fi

#Setup server maintenance scripts
~/setup_gcp/scripts/buildLocalServer/maintenance.sh

echo " * Config File: $F"
exit 0
