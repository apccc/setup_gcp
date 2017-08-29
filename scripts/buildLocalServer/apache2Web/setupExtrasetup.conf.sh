#!/bin/bash
source ~/setup_gcp/settings/core.sh

FORIG=/etc/apache2/conf-available/extrasetup.conf
F=/tmp/extrasetupconf.txt
if [ ! -f $F ];then
        echo "Creating \"$FORIG\"."
        touch $F
        echo '<IfModule mod_expires.c>' >> $F
        echo '   ExpiresActive on' >> $F
        echo '   ExpiresDefault "now"' >> $F
        echo '   ExpiresByType image/gif "access plus 2 months"' >> $F
        echo '   ExpiresByType image/jpeg "access plus 2 months"' >> $F
        echo '   ExpiresByType image/png "access plus 2 months"' >> $F
        echo '   ExpiresByType image/x-icon "access plus 2 months"' >> $F
        echo '   ExpiresByType text/css "access plus 2 months"' >> $F
        echo '   ExpiresByType text/javascript "access plus 2 months"' >> $F
        echo '   ExpiresByType application/javascript "access plus 2 months"' >> $F
        echo '   ExpiresByType application/x-shockwave-flash "access plus 2 months"' >> $F
        echo '   ExpiresByType application/vnd.ms-fontobject "access plus 2 months"' >> $F
        echo '   ExpiresByType font/truetype "access plus 2 months"' >> $F
        echo '   ExpiresByType font/opentype "access plus 2 months"' >> $F
        echo '   ExpiresByType application/x-font-woff "access plus 2 months"' >> $F
        echo '   ExpiresByType image/svg+xml "access plus 2 months"' >> $F
        echo '   ExpiresByType text/html "now"' >> $F
        echo '   ExpiresByType application/x-httpd-php "now"' >> $F
        echo '</IfModule>' >> $F
        echo '' >> $F
        echo 'FileETag none' >> $F
        echo '' >> $F
        echo 'ServerTokens Prod' >> $F
        echo '' >> $F
        echo '<IfModule mod_headers.c>' >> $F
        echo '   <FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf|eot|otf|woff|ttf|svg)$">' >> $F
        echo '      Header set Cache-Control "public, max-age=5184000"' >> $F
        echo '   </FilesMatch>' >> $F
        echo '   <FilesMatch "\.(eot|otf|woff|ttf|svg)$">' >> $F
        echo '      SetEnvIf Origin "http(s)?://([a-zA-Z0-9_]+)(\.'`echo $COMPANY_DOMAIN | sed 's/\./\\./g'`')$" origin_is=$0' >> $F
        echo '      Header set Access-Control-Allow-Origin %{origin_is}e env=origin_is' >> $F
        echo '   </FilesMatch>' >> $F
        echo '   Header unset Server' >> $F
        echo '   Header unset X-Powered-By' >> $F
        echo '   SetEnvIf Range "(,.*?){10,}" bad-range=1' >> $F
        echo '   RequestHeader unset Range env=bad-range' >> $F
        echo '</IfModule>' >> $F
        echo '' >> $F
        echo '<IfModule mod_mime.c>' >> $F
        echo -e '   AddType application/vnd.ms-fontobject\teot' >> $F
        echo -e '   AddType font/truetype\t\t\tttf' >> $F
        echo -e '   AddType font/opentype\t\t\totf' >> $F
        echo -e '   AddType application/x-font-woff\t\twoff' >> $F
        echo -e '   AddType image/svg+xml\t\t\tsvg svgz' >> $F
        echo '</IfModule>' >> $F
        echo '<IfModule mod_php5.c>' >> $F
        echo '  php_value short_open_tag 1' >> $F
        echo '</IfModule>' >> $F
        echo ''
fi
sudo cp "$F" "$FORIG"
rm "$F"
sudo ln -s -v /etc/apache2/conf-available/extrasetup.conf /etc/apache2/conf-enabled/extrasetup.conf

exit 0
