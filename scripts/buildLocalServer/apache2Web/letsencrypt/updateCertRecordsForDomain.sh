#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

ZDOMAIN=`echo "$1" | egrep -m1 -oe '[a-z0-9.]*'`

echo " * Updating Cert Records for $ZDOMAIN"

#update our records
if [[ `sudo ls -1 /root/ssl | wc -l` -lt 1 ]];then
  echo " * Making directory /root/ssl"
  sudo mkdir /root/ssl
fi

if [[ `sudo ls -1 "/root/ssl/$ZDOMAIN" | wc -l` -lt 1 ]];then
  echo " * Making directory /root/ssl/$ZDOMAIN"
  sudo mkdir "/root/ssl/$ZDOMAIN"
fi

#check if we do not have the main domain directory, but we do have one of the domains directory, which may be used
if [[ `sudo ls -1 "/etc/letsencrypt/live/$ZDOMAIN" | wc -l` -lt 1 ]] && [ ! -z "$2" ];then
  ZDOMAINS=`echo "$2" | egrep -m1 -oe '[a-z0-9.,]*'`
  for X in `echo "$ZDOMAINS" | tr ',' "\n"`;do
    ZDIR=`sudo ls -1 /etc/letsencrypt/live/ | grep "$X"`
    if [ ! -z "$ZDIR" ] && [[ `sudo ls -1 "/etc/letsencrypt/live/$ZDIR" | wc -l` -gt 0 ]];then
      echo " * FOUND: /etc/letsencrypt/live/$ZDIR"
      sudo cp -rL "/etc/letsencrypt/live/$ZDIR/" "/tmp/"
      sudo mv "/tmp/$ZDIR" "/etc/letsencrypt/live/$ZDOMAIN"
      sudo rm -r "/etc/letsencrypt/live/$ZDIR"
      break
    fi
  done
elif [[ `sudo ls -1 "/etc/letsencrypt/live/$ZDOMAIN" | wc -l` -lt 1 ]];then
  ZDIR=`sudo ls -1 /etc/letsencrypt/live/ | grep "$ZDOMAIN"`
  if [ ! -z "$ZDIR" ] && [[ `sudo ls -1 "/etc/letsencrypt/live/$ZDIR" | wc -l` -gt 0 ]];then
    echo " * FOUND: /etc/letsencrypt/live/$ZDIR"
    sudo cp -rL "/etc/letsencrypt/live/$ZDIR/" "/tmp/"
    sudo mv "/tmp/$ZDIR" "/etc/letsencrypt/live/$ZDOMAIN"
    sudo rm -r "/etc/letsencrypt/live/$ZDIR"
  fi
fi

F="/etc/letsencrypt/live/$ZDOMAIN/fullchain.pem"
if [[ `sudo ls -1 "$F" | wc -l` -gt 0 ]];then
  #X=`cat $F`
  #CMD="UPDATE cowSystem.serverWebSites SET SSLCertificateFile='$X' WHERE server='$USERNAME' AND subdomain='$ZDOMAIN' LIMIT 1;"
  #COMMAND=`$MY "$CMD"`
  echo " * Putting file in place /root/ssl/$ZDOMAIN/CertificateFile.crt"
  sudo cp "$F" "/root/ssl/$ZDOMAIN/CertificateFile.crt"
fi

F="/etc/letsencrypt/live/$ZDOMAIN/privkey.pem"
if [[ `sudo ls -1 "$F" | wc -l` -gt 0 ]];then
  #X=`cat $F`
  #CMD="UPDATE cowSystem.serverWebSites SET SSLCertificateKey=AES_ENCRYPT('$X','$SSLCERTIFICATEKEYAESKEY') WHERE server='$USERNAME' AND subdomain='$ZDOMAIN' LIMIT 1;"
  #COMMAND=`$MY "$CMD"`
  echo " * Putting file in place /root/ssl/$ZDOMAIN/CertificateKey.key"
  sudo cp "$F" "/root/ssl/$ZDOMAIN/CertificateKey.key"
fi

F="/etc/letsencrypt/live/$ZDOMAIN/chain.pem"
if [[ `sudo ls -1 "$F" | wc -l` -gt 0 ]];then
  #X=`cat $F`
  #CMD="UPDATE cowSystem.serverWebSites SET SSLCertificateChainFile='$X' WHERE server='$USERNAME' AND subdomain='$ZDOMAIN' LIMIT 1;"
  #COMMAND=`$MY "$CMD"`
  echo " * Putting file in place /root/ssl/$ZDOMAIN/CertificateChain.txt"
  sudo cp "$F" "/root/ssl/$ZDOMAIN/CertificateChain.txt"
fi

#clean out the source certs
if [[ `sudo ls -1 "/etc/letsencrypt/live/$ZDOMAIN" | wc -l` -gt 1 ]];then
  sudo rm -r "/etc/letsencrypt/live/$ZDOMAIN"
fi

exit 0
