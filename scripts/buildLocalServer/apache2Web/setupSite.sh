#!/bin/bash

source ~/setup_gcp/settings/core.sh

SUBDOMAIN="$1"
if [ -z "$SUBDOMAIN" ];then
  echo " * ERROR: Subdomain not set for setupSite"
  exit 1
fi

echo " * Setting up site $SUBDOMAIN"
ALLOWOVERRIDE=$($MY 'SELECT `AllowOverride` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
CRONJOBS=$($MY 'SELECT `cronjobs` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
REWRITES=$($MY 'SELECT `rewrites` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
SSL=$($MY 'SELECT `SSL` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
RENEWSSL=$($MY 'SELECT `renew_SSL` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
ALIASES=$($MY 'SELECT `aliases` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
VIRTUALHOSTEXTRAS=$($MY 'SELECT `virtualhost_extras` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
DEPENDENCYREPOSITORIES=$($MY 'SELECT `dependencies_repositories` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
DEPENDENCYPACKAGES=$($MY 'SELECT `dependencies_packages` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
SITETEMPLATE=$($MY 'SELECT `template` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)
SITEDATABASE=$($MY 'SELECT `database` FROM `'"$SYSTEM_DATABASE"'`.`sites` WHERE `subdomain`="'$SUBDOMAIN'" LIMIT 1' | tail -n 1)

#set defaults
if [ -z "$ALLOWOVERRIDE" ];then
  ALLOWOVERRIDE="None"
fi

#setup the site directory
D="/var/www/$SUBDOMAIN"
if [ ! -d "$D" ];then
  sudo mkdir "$D"
fi
#setup the Web files for the site
if [ ! -d "$D/htdocs" ];then
  if [ ! -z "${SITETEMPLATE}" ];then
    X=~/setup_gcp/settings/server/${SITETEMPLATE}SiteTemplate/htdocs
  fi
  if [ ! -z "${SITETEMPLATE}" ] && [ -d $X ];then
    sudo rsync -av "$X" "$D/"
  else
    sudo mkdir "$D/htdocs"
  fi
fi

#setup the site database
if [ ! -z "$SITEDATABASE" ] && [ ! -z "${SITETEMPLATE}" ];then
  #if the database does not exist and is set
  if [[ `$MY "SHOW DATABASES LIKE '${SITEDATABASE}';" | tail -n 1 | wc -l` -lt 1 ]];then
    #look for the execution file
    F=~/setup_gcp/settings/server/${SITETEMPLATE}SiteTemplate/databaseSetup.sh
    if [ -f $F ];then
    echo " * Setting up site template database."
      $F "${SITEDATABASE}"
    fi
  fi
fi

#setup the Web settings for the site
if [ ! -z "${SITETEMPLATE}" ];then
  F=~/setup_gcp/settings/server/${SITETEMPLATE}SiteTemplate/settingsSetup.sh
  if [ -f $F ];then
    echo " * Setting up site template settings."
    $F "${SUBDOMAIN}"
  fi
fi

#setup the site conf file
if [ "$SUBDOMAIN" == "${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}" ];then
  F="/etc/apache2/sites-enabled/000-default.conf"
else
  F="/etc/apache2/sites-enabled/${SUBDOMAIN}.conf"
fi

if [ ! -z "$CRONJOBS" ];then
  T=/tmp/cronjobs.tmp
  echo -e "$CRONJOBS" > $T
  while read X;do
    X=`echo "$X" | sed 's/^\s*//g' | sed 's/\s*$//g'`
    $AC "$X"
  done < "$T"
  rm $T
fi

if [ "$SSL" == "T" ];then
  PRIMARYPORT="443"
else
  PRIMARYPORT="80"
fi

echo '<VirtualHost *:'"$PRIMARYPORT"'>' | sudo tee $F > /dev/null
echo " ServerName $SUBDOMAIN" | sudo tee -a $F > /dev/null
if [ ! -z "$ALIASES" ];then
  for A in $(echo -e "$ALIASES");do
    echo " ServerAlias $A" | sudo tee -a $F > /dev/null
  done
fi
echo " ServerAdmin $COMPANY_SYSADMIN_EMAIL" | sudo tee -a $F > /dev/null
echo " DocumentRoot $D/htdocs" | sudo tee -a $F > /dev/null
echo " <Directory $D/htdocs>" | sudo tee -a $F > /dev/null
if [ "$ALLOWOVERRIDE" == "All" ];then
  echo '  Options +FollowSymLinks -MultiViews -Includes -Indexes' | sudo tee -a $F > /dev/null
else
  echo '  Options -FollowSymLinks -MultiViews -Includes -Indexes' | sudo tee -a $F > /dev/null
fi
echo "  AllowOverride $ALLOWOVERRIDE" | sudo tee -a $F > /dev/null
echo ' </Directory>' | sudo tee -a $F > /dev/null
echo ' <IfModule mod_ssl.c>' | sudo tee -a $F > /dev/null
if [ "$SSL" == "T" ];then
  echo '  SSLEngine On' | sudo tee -a $F > /dev/null
  echo "  SSLCertificateFile /root/ssl/$SUBDOMAIN/CertificateFile.crt" | sudo tee -a $F > /dev/null
  echo "  SSLCertificateKeyFile /root/ssl/$SUBDOMAIN/CertificateKey.key" | sudo tee -a $F > /dev/null
  echo "  SSLCertificateChainFile /root/ssl/$SUBDOMAIN/CertificateChain.txt" | sudo tee -a $F > /dev/null
else
  echo '  SSLEngine off' | sudo tee -a $F > /dev/null
fi
echo ' </IfModule>' | sudo tee -a $F > /dev/null
echo ' ErrorLog ${APACHE_LOG_DIR}/error.log' | sudo tee -a $F > /dev/null
echo ' CustomLog ${APACHE_LOG_DIR}/access.log combined' | sudo tee -a $F > /dev/null
if [ ! -z "$VIRTUALHOSTEXTRAS" ];then
  T=/tmp/vhostextras.tmp
  echo -e "$VIRTUALHOSTEXTRAS" > $T
  while read X;do
    echo " $X" | sudo tee -a $F > /dev/null
  done < "$T"
  rm $T
fi
if [ ! -z "$REWRITES" ];then
  echo ' RewriteEngine On' | sudo tee -a $F > /dev/null
  T=/tmp/rewrites.tmp
  echo -e "$REWRITES" > $T
  while read X;do
    echo " $X" | sudo tee -a $F > /dev/null
  done < "$T"
  rm $T
else
  if [ "$ALLOWOVERRIDE" == "All" ];then
    echo ' RewriteEngine On' | sudo tee -a $F > /dev/null
  else
    echo ' RewriteEngine Off' | sudo tee -a $F > /dev/null
  fi
fi
echo '</VirtualHost>' | sudo tee -a $F > /dev/null

if [ "$SSL" == "T" ];then
  echo '<VirtualHost *:80>' | sudo tee -a $F > /dev/null
  echo " ServerName $SUBDOMAIN" | sudo tee -a $F > /dev/null
  if [ ! -z "$ALIASES" ];then
    for A in $(echo -e "$ALIASES");do
      echo " ServerAlias $A" | sudo tee -a $F > /dev/null
    done
  fi
  echo " ServerAdmin $COMPANY_SYSADMIN_EMAIL" | sudo tee -a $F > /dev/null
  echo " DocumentRoot $D/htdocs" | sudo tee -a $F > /dev/null
  echo " <Directory $D/htdocs>" | sudo tee -a $F > /dev/null
  if [ "$ALLOWOVERRIDE" == "All" ];then
    echo '  Options +FollowSymLinks -MultiViews -Includes -Indexes' | sudo tee -a $F > /dev/null
  else
    echo '  Options -FollowSymLinks -MultiViews -Includes -Indexes' | sudo tee -a $F > /dev/null
  fi
  echo "  AllowOverride $ALLOWOVERRIDE" | sudo tee -a $F > /dev/null
  echo ' </Directory>' | sudo tee -a $F > /dev/null
  echo ' <IfModule mod_ssl.c>' | sudo tee -a $F > /dev/null
  echo '  SSLEngine off' | sudo tee -a $F > /dev/null
  echo ' </IfModule>' | sudo tee -a $F > /dev/null
  echo ' ErrorLog ${APACHE_LOG_DIR}/error.log' | sudo tee -a $F > /dev/null
  echo ' CustomLog ${APACHE_LOG_DIR}/access.log combined' | sudo tee -a $F > /dev/null
  echo ' RewriteEngine On' | sudo tee -a $F > /dev/null
  echo ' RewriteRule ^/(.*) https://'"$SUBDOMAIN"'/$1 [R=301,L]' | sudo tee -a $F > /dev/null
  echo '</VirtualHost>' | sudo tee -a $F > /dev/null

  #get a cert, if needed
  if [[ `sudo ls -1 "/root/ssl/$SUBDOMAIN" | wc -l` -lt 1 ]] || [ "$RENEWSSL" == "T" ];then
    echo " * SSL Cert Needed for $SUBDOMAIN - will try to obtain one:"
    if [ ! -z "$ALIASES" ];then
      X=`echo -e "${ALIASES}\n${SUBDOMAIN}" | tr "\n" ',' | tr ",," "," | sed 's/,$//'`
      ~/setup_gcp/scripts/buildLocalServer/apache2Web/letsencrypt/getCertForDomains.sh "$SUBDOMAIN" "$X"
    else
      ~/setup_gcp/scripts/buildLocalServer/apache2Web/letsencrypt/getCertForDomain.sh "$SUBDOMAIN"
    fi
  fi
fi

#setup dependencies
if [ ! -z "$DEPENDENCYREPOSITORIES" ];then
  T=/tmp/setupextras.tmp
  echo -e "$DEPENDENCYREPOSITORIES" > $T
  while read X;do
    echo "$DEPENDENCYREPOSITORIES-: $X"
    X=`echo "$X" | tr -d '\n' | tr -d '\r'`
    if [[ `$IN "$X" "/etc/apt/sources.list"` -lt 1 ]];then
      echo " * Adding repository."
      echo "$X" | sudo tee -a /etc/apt/sources.list
      sudo apt-get update
    fi
  done < "$T"
  rm $T
fi

if [ ! -z "$DEPENDENCYPACKAGES" ];then
  T=/tmp/setupextras.tmp
  echo -e "$DEPENDENCYPACKAGES" > $T
  echo "Installing Packaged:"
  cat $T
  while read X;do
    X=`echo "$X" | tr -d '\n' | tr -d '\r'`
    echo " * Installing $X"
    sudo apt-get install -y --force-yes "$X"
  done < "$T"
  rm $T
fi

if [ ! -z "$DEPENDENCYCOMMANDS" ];then
  T=/tmp/setupextras.tmp
  echo -e "$DEPENDENCYCOMMANDS" > $T
  while read X;do
    X=`echo "$X" | tr -d '\n' | tr -d '\r'`
    if [ -z "$X" ];then continue; fi
    if [ "${X:0:1}" == "#" ];then echo "$X";continue; fi
    echo "Running Command: $X"
    sudo eval "$X"
    echo ''
  done < "$T"
  rm $T
fi
exit 0
