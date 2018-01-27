#!/bin/bash

#install postfix for mailgun relay

source ~/setup-config/setup_gcp/core.sh

if [ -z "$MAILGUN_SMTP_USERNAME" ] || [ -z "$MAILGUN_SMTP_PASSWORD" ];then
  echo "Mailgun settings not found!"
  exit 1
fi

echo " * Installing Postfix Mailgun Relay System"

#install postfix
sudo debconf-set-selections <<< "postfix postfix/mailname string $(hostname)"
sudo debconf-set-selections <<< "postfix postfix/main_mailer_type string 'No configuration'"
sudo apt-get install -y postfix libsasl2-modules
if [ ! -f /etc/postfix/main.cf ] || [ `grep mailgun /etc/postfix/main.cf | wc -l` -lt 1 ];then
  echo "relayhost = [smtp.mailgun.org]:2525" | sudo tee -a /etc/postfix/main.cf > /dev/null 2>&1
  echo "smtp_tls_security_level = encrypt" | sudo tee -a /etc/postfix/main.cf > /dev/null 2>&1
  echo "smtp_sasl_auth_enable = yes" | sudo tee -a /etc/postfix/main.cf > /dev/null 2>&1
  echo "smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd" | sudo tee -a /etc/postfix/main.cf > /dev/null 2>&1
  echo "smtp_sasl_security_options = noanonymous" | sudo tee -a /etc/postfix/main.cf > /dev/null 2>&1

  #setup mailgun creds info
  echo "[smtp.mailgun.org]:2525 ${MAILGUN_SMTP_USERNAME}:${MAILGUN_SMTP_PASSWORD}" | sudo tee /etc/postfix/sasl_passwd > /dev/null 2>&1
  sudo postmap /etc/postfix/sasl_passwd
  sudo rm /etc/postfix/sasl_passwd
  sudo chmod 600 /etc/postfix/sasl_passwd.db

  #restart postfix to take new configuration
  sudo /etc/init.d/postfix restart

  #install mailutils for command line mail service
  sudo apt-get install mailutils -y
fi

echo " * Done Installing Postfix Mailgun Relay System"

exit 0
