#!/bin/bash

echo 'phpmyadmin phpmyadmin/internal/skip-preseed boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean false' | debconf-set-selections
apt-get install -y phpmyadmin

exit 0
