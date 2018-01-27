#!/bin/bash

source ~/setup-config/setup_gcp/core.sh

echo " * Setting up database for default Web site"

#create the database, if necessary
X="CREATE DATABASE IF NOT EXISTS $SYSTEM_DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$MY "$X"

#create the user, if necessary
if [[ `$MY "SELECT User FROM mysql.user WHERE User LIKE '${MYSQL_WEB_USER}';" | tail -n 2 | wc -l` -lt 1 ]];then
  #create the web user
  X="CREATE USER '${MYSQL_WEB_USER}'@'%' IDENTIFIED BY '${MYSQL_WEB_USER_PASS}';"
  $MY "$X"

  X="GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, FILE, INDEX, ALTER, CREATE TEMPORARY TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE "
  X=$X"ON *.* TO '${MYSQL_WEB_USER}'@'%' IDENTIFIED BY '${MYSQL_WEB_USER_PASS}';"
  $MY "$X"
fi

#create the user table, if this does not exist
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'users';" | tail -n +2 | wc -l` -lt 1 ]];then
  X="CREATE TABLE IF NOT EXISTS ${SYSTEM_DATABASE}.users ("
  X=$X'`id` bigint(10) NOT NULL,'
  X=$X'`first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`password` varbinary(512) NOT NULL,'
  X=$X'`nonce` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`url` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "F",'
  X=$X'`is_admin` enum("T","F") COLLATE utf8_unicode_ci NOT NULL DEFAULT "F",'
  X=$X'`variables` TEXT NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X="ALTER TABLE ${SYSTEM_DATABASE}.users ADD PRIMARY KEY (id), ADD UNIQUE KEY email (email);"
  $MY "$X"

  X="ALTER TABLE ${SYSTEM_DATABASE}.users MODIFY id bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;"
  $MY "$X"

  NONCE=`tr -cd [:alnum:] < /dev/urandom | head -c 250`
  PW_HASH=`echo -n "${SYSADMIN_INIT_PASS}${NONCE}" | sha512sum | cut -d' ' -f1`

  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`users` '
  X=$X'(`first_name`,`last_name`,`email`,`password`,`nonce`,`active`,`is_admin`) '
  X=$X"VALUES ('System','Administrator','${COMPANY_SYSADMIN_EMAIL}','${PW_HASH}','${NONCE}','T','T');"
  $MY "$X"
fi

#create the login tries table, if this does not exist
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'login_tries';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`login_tries` ('
  X=$X'`id` bigint(10) NOT NULL,'
  X=$X'`email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=155051 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`login_tries` ADD PRIMARY KEY (`id`), ADD KEY `ip` (`ip`), ADD KEY `timestamp` (`timestamp`), ADD KEY `email` (`email`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`login_tries` MODIFY `id` bigint(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;'
  $MY "$X"
fi

#create the whitelisted ips
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'whitelisted_ips';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`whitelisted_ips` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`whitelisted_ips` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `ip` (`ip`), ADD KEY `timestamp` (`timestamp`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`whitelisted_ips` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  for ZIP in `echo "$WHITELISTEDIPS" | tr ' ' '\n'`;do
    echo " * Whitelisting IP $ZIP"
    X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`whitelisted_ips` (`ip`) VALUES ("'"$ZIP"'");'
    $MY "$X"
 done
fi


#create the CSS table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'css';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`css` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`sheet` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`ord` int(10) NOT NULL DEFAULT "0",'
  X=$X'`whitelisted_ips` TEXT NOT NULL,'
  X=$X'`lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`css` ADD PRIMARY KEY (`id`), ADD KEY `ord` (`ord`), ADD KEY `lastUpdated` (`lastUpdated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`css` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the JS table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'js';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`js` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`script` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`ord` int(10) NOT NULL DEFAULT "0",'
  X=$X'`whitelisted_ips` TEXT NOT NULL,'
  X=$X'`lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`js` ADD PRIMARY KEY (`id`), ADD KEY `ord` (`ord`), ADD KEY `lastUpdated` (`lastUpdated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`js` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the documentation table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'documentation';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`documentation` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`documentation` ADD PRIMARY KEY (`id`), ADD KEY `category` (`category`), ADD KEY `updated` (`updated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`documentation` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the sites table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'sites';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`sites` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`subdomain` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`aliases` TEXT NOT NULL,'
  X=$X'`SSL` ENUM("T","F") NOT NULL DEFAULT "T",'
  X=$X'`renew_SSL` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "F",'
  X=$X'`AllowOverride` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "None",'
  X=$X'`cronjobs` TEXT NOT NULL,'
  X=$X'`rewrites` TEXT NOT NULL,'
  X=$X'`virtualhost_extras` TEXT NOT NULL,'
  X=$X'`dependencies_repositories` TEXT NOT NULL,'
  X=$X'`dependencies_packages` TEXT NOT NULL,'
  X=$X'`dependencies_commands` TEXT NOT NULL,'
  X=$X'`template` VARCHAR(150) NOT NULL DEFAULT "default",'
  X=$X'`database` VARCHAR(100) NOT NULL,'
  X=$X'`logo` BLOB NOT NULL,'
  X=$X'`favicon` BLOB NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`sites` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `subdomain` (`subdomain`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`sites` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  #setup default site entry
  RWTMP='/tmp/rwtmp.txt'
  touch $RWTMP
  echo 'RewriteRule ^/log$ /loginPage.php?%{QUERY_STRING} [PT,L]' >> $RWTMP
  echo 'RewriteRule ^/js/script([0-9-]+).js$ /jsPage.php?q=$1&%{QUERY_STRING} [PT,L]' >> $RWTMP
  echo 'RewriteRule ^/css/style([0-9-]+).css$ /cssPage.php?q=$1&%{QUERY_STRING} [PT,L]' >> $RWTMP
  echo 'RewriteRule ^/favicon.ico$ /a64File.php?f=favicon&t=sites&id=1&%{QUERY_STRING} [PT,L]' >> $RWTMP
  echo 'RewriteRule ^/afile/([a-z0-9_-]+)/([0-9]+).([a-z0-9_-]+).([a-z0-9]+)$ /a64File.php?f=$3&t=$1&id=$2&ext=$4&%{QUERY_STRING} [PT,L]' >> $RWTMP
  R=`cat "$RWTMP"`
  rm $RWTMP

  CRTMP='/tmp/crtmp.txt'
  touch $CRTMP
  echo '2 2 * * * ~/setup_gcp/scripts/cron/backupSystem.sh > ~/cron.backupSystem.log 2>&1' >> $CRTMP
  echo '3 3 3 * * ~/setup_gcp/scripts/cron/renewSSLCerts.sh > ~/cron.renewSSLCerts.log 2>&1' >> $CRTMP
  echo '4 4 * * 6 ~/setup_gcp/scripts/cron/updateSystem.sh > ~/cron.updateSystem.log 2>&1' >> $CRTMP
  C=`cat "$CRTMP"`
  rm $CRTMP

  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`sites` (`subdomain`,`cronjobs`,`rewrites`) VALUES ("'"${COMPANY_ADMIN_SUBDOMAIN}.${COMPANY_DOMAIN}"'","'"$C"'","'"$R"'");'
  $MY "$X"
fi

#create the cache table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'cache';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`cache` ('
  X=$X'`id` bigint(20) unsigned NOT NULL,'
  X=$X'`k` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`data` longblob NOT NULL,'
  X=$X'`expires` datetime NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`cache` ADD PRIMARY KEY (`id`), ADD KEY `k` (`k`), ADD KEY `expires` (`expires`), ADD KEY `timestamp` (`timestamp`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`cache` MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the site sections table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'site_sections';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`site_sections` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`site_id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`css_ids` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`js_ids` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`site_sections` ADD PRIMARY KEY (`id`), ADD KEY `site_id` (`site_id`), ADD KEY `identifier` (`identifier`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`site_sections` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the site pages table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'site_pages';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`site_pages` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`site_section_id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`keywords` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`code` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`site_pages` ADD PRIMARY KEY (`id`), ADD KEY `site_section_id` (`site_section_id`), ADD KEY `identifier` (`identifier`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`site_pages` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the navigation table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'navigation';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`navigation` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`site_id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`href` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`content` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`navigation` ADD PRIMARY KEY (`id`), ADD KEY `site_id` (`site_id`), ADD KEY `identifier` (`identifier`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`navigation` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create Google Cloud Storage Buckets table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'googleCloudStorage_buckets';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`storageClass` enum("DURABLE_REDUCED_AVAILABILITY","STANDARD") COLLATE utf8_unicode_ci NOT NULL DEFAULT "STANDARD",'
  X=$X'`bucketLocation` enum("EU","US","US-EAST1","US-EAST2","US-EAST3","US-CENTRAL1","US-CENTRAL2","US-WEST1") COLLATE utf8_unicode_ci NOT NULL DEFAULT "US"'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  BID="1"
  BNAME="zstore_${STORAGE_IDENTIFIER}_var_www"
  BCLASS="DURABLE_REDUCED_AVAILABILITY"
  BLOCATION="US"
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` (`id`,`name`,`storageClass`,`bucketLocation`) '
  X=$X'VALUES ("'"$BID"'","'"$BNAME"'","'"$BCLASS"'","'"$BLOCATION"'");'
  $MY "$X"
  gsutil mb -p "$(~/setup_gcp/settings/get/gcloud/project-id.sh)" -c "$BCLASS" -l "$BLOCATION" "gs://${BNAME}/"
fi
HOMEBNAME="zstore_${STORAGE_IDENTIFIER}_home"
HOMEBID=`$MY "SELECT (max(id)+1) AS z FROM ${SYSTEM_DATABASE}.googleCloudStorage_buckets" | tail -n 1`
if [[ $HOMEBID -gt 0 ]] && [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleCloudStorage_buckets WHERE name='$HOMEBNAME'" | tail -n +2 | wc -l` -lt 1 ]];then
  BID=$HOMEBID
  BNAME=$HOMEBNAME
  BCLASS="DURABLE_REDUCED_AVAILABILITY"
  BLOCATION="US"
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_buckets` (`id`,`name`,`storageClass`,`bucketLocation`) '
  X=$X'VALUES ("'"$BID"'","'"$BNAME"'","'"$BCLASS"'","'"$BLOCATION"'");'
  $MY "$X"
  gsutil mb -p "$(~/setup_gcp/settings/get/gcloud/project-id.sh)" -c "$BCLASS" -l "$BLOCATION" "gs://${BNAME}/"
else
  HOMEBID=0
fi


#create Google Cloud Storage Backup Schedule table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'googleCloudStorage_backupSchedule';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`server` varchar(50) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`path` varchar(200) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`pathsToOmit` text COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`bucket_id` int(10) NOT NULL,'
  X=$X'`lastRun` datetime NOT NULL,'
  X=$X'`nextRun` datetime NOT NULL,'
  X=$X'`runFrequencyDays` int(10) NOT NULL DEFAULT "1"'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` ADD PRIMARY KEY (`id`), ADD KEY `server` (`server`), ADD KEY `nextRun` (`nextRun`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` (`server`,`path`,`bucket_id`,`nextRun`) '
  X=$X'VALUES ("'"$(hostname)"'","/var/www","1",NOW());'
  $MY "$X"
fi
if [[ $HOMEBID -gt 0 ]] && [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleCloudStorage_backupSchedule WHERE server='$(hostname)'" | tail -n +2 | wc -l` -lt 1 ]];then
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleCloudStorage_backupSchedule` (`server`,`path`,`bucket_id`,`nextRun`) '
  X=$X'VALUES ("'"$(hostname)"'","/home","$HOMEBID",NOW());'
  $MY "$X"
fi


#create Google Cloud Compute Engine VM Instances table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'googleComputeEngine_VMInstances';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_VMInstances` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`zone` enum("europe-west1-a","europe-west1-b","us-central1-a","us-central1-b","us-west1-a") COLLATE utf8_unicode_ci NOT NULL DEFAULT "us-west1-a",'
  X=$X'`region` enum("us-central1","us-west1","europe-west1") COLLATE utf8_unicode_ci NOT NULL DEFAULT "us-west1",'
  X=$X'`machine_type` enum("g1-small","n1-standard-1","f1-micro","n1-highcpu-2","n1-standard-2","n1-highmem-2","n1-highcpu-4","n1-highmem-4","n1-standard-4","n1-highmem-8","n1-highcpu-8","n1-standard-8") COLLATE utf8_unicode_ci NOT NULL DEFAULT "f1-micro",'
  X=$X'`image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`service_account_scopes` varchar(250) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8_unicode_ci NOT NULL DEFAULT "F"'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_VMInstances` ADD PRIMARY KEY (`id`), ADD KEY `name` (`name`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_VMInstances` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi
if [[ `$MY "SELECT id FROM ${SYSTEM_DATABASE}.googleComputeEngine_VMInstances WHERE name='$(hostname)'" | tail -n +2 | wc -l` -lt 1 ]];then
  X='INSERT INTO `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_VMInstances` (`name`,`zone`,`region`,`machine_type`,`ip`,`active`) '
  X=$X'VALUES ("'"$(hostname)"'","'"$(~/setup_gcp/settings/get/gcloud/zone.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/region.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/machine-type.sh)"'","'"$(~/setup_gcp/settings/get/gcloud/ip.sh)"'","T");'
  $MY "$X"
fi


#create Google Cloud Compute Engine Service Account Scopes table
if [[ `$MY "USE ${SYSTEM_DATABASE};SHOW TABLES LIKE 'googleComputeEngine_ServiceAccountScopes';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_ServiceAccountScopes` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`service` varchar(50) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`full_scope` varchar(100) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`alias` varchar(50) COLLATE utf8_unicode_ci NOT NULL,'
  X=$X'`description` varchar(200) COLLATE utf8_unicode_ci NOT NULL'
  X=$X') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_ServiceAccountScopes` ADD PRIMARY KEY (`id`);'
  $MY "$X"

  X='ALTER TABLE `'"${SYSTEM_DATABASE}"'`.`googleComputeEngine_ServiceAccountScopes` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  X='INSERT INTO `googleComputeEngine_ServiceAccountScopes` (`id`, `service`, `full_scope`, `alias`, `description`) VALUES'
  X=$X"(1, 'Google BigQuery', 'https://www.googleapis.com/auth/bigquery', 'bigquery', 'Access to Google BigQuery API'),"
  X=$X"(2, 'Google Cloud SQL', 'https://www.googleapis.com/auth/sqlservice', 'cloudsql', 'Access to Google Cloud SQL API'),"
  X=$X"(3, 'Google Compute Engine', 'https://www.googleapis.com/auth/compute.readonly', 'compute-ro', 'Read-only access to Google Compute Engine'),"
  X=$X"(4, 'Google Compute Engine', 'https://www.googleapis.com/auth/compute', 'compute-rw', 'Read-write access to Google Compute Engine'),"
  X=$X"(5, 'Google Cloud Storage', 'https://www.googleapis.com/auth/devstorage.read_only', 'storage-ro', 'Read-only access to Google Cloud Storage'),"
  X=$X"(6, 'Google Cloud Storage', 'https://www.googleapis.com/auth/devstorage.read_write', 'storage-rw', 'Read-write access to Google Cloud Storage'),"
  X=$X"(7, 'Google Cloud Storage', 'https://www.googleapis.com/auth/devstorage.full_control', 'storage-full', 'Full access to Google Cloud Storage'),"
  X=$X"(8, 'Google App Engine Task Queue', 'https://www.googleapis.com/auth/taskqueue', 'taskqueue', 'Access to Google App Engine Task Queue API');"
  $MY "$X"
fi


echo " * Done setting up database for default Web site"

exit 0
