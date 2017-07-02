#!/bin/bash
#builderOne
source ~/setup/settings/core.sh

if [ -z "$1" ];then
  echo "Site database name not set!"
  exit 1
fi

SITE_DATABASENAME=`echo "$1" | egrep -oe '[a-zA-Z0-9_]+'`
if [ "$SITE_DATABASENAME" != "$1" ];then
  echo "Database name is invalid!"
  exit 1
fi

echo " * Setting up database $SITE_DATABASENAME for blank site template"

#create the database, if necessary
X="CREATE DATABASE IF NOT EXISTS $SITE_DATABASENAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$MY "$X"

#create the CSS table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'css';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`css` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`sheet` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`ord` int(10) NOT NULL DEFAULT "0",'
  X=$X'`whitelisted_ips` TEXT NOT NULL,'
  X=$X'`lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`css` ADD PRIMARY KEY (`id`), ADD KEY `ord` (`ord`), ADD KEY `lastUpdated` (`lastUpdated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`css` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi

#create the JS table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'js';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`js` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`script` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`ord` int(10) NOT NULL DEFAULT "0",'
  X=$X'`whitelisted_ips` TEXT NOT NULL,'
  X=$X'`lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`js` ADD PRIMARY KEY (`id`), ADD KEY `ord` (`ord`), ADD KEY `lastUpdated` (`lastUpdated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`js` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi

#create the documentation table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'documentation';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`documentation` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`documentation` ADD PRIMARY KEY (`id`), ADD KEY `category` (`category`), ADD KEY `updated` (`updated`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`documentation` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi

#create the site sections table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'site_sections';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`site_sections` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`css_ids` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`js_ids` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`site_sections` ADD PRIMARY KEY (`id`), ADD KEY `identifier` (`identifier`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`site_sections` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi

#create the site pages table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'site_pages';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`site_pages` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`site_section_id` int(10) unsigned NOT NULL DEFAULT "0",'
  X=$X'`name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",'
  X=$X'`title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",'
  X=$X'`description` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",'
  X=$X'`keywords` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",'
  X=$X'`code` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`code_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "HTML",'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`site_pages` ADD PRIMARY KEY (`id`), ADD KEY `site_section_id` (`site_section_id`), ADD KEY `identifier` (`identifier`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`site_pages` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi

#create the navigation table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'navigation';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`navigation` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`parent` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`description` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`href` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`content` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`ord` int(10) unsigned NOT NULL DEFAULT "0",'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T"'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`navigation` ADD PRIMARY KEY (`id`), ADD KEY `parent` (`parent`), ADD KEY `identifier` (`identifier`), ADD KEY `ord` (`ord`), ADD KEY `active` (`active`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`navigation` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the images table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'images';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`images` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`active` enum("T","F") COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "T",'
  X=$X'`im` LONGBLOB NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`images` ADD PRIMARY KEY (`id`), ADD KEY `active` (`active`), ADD KEY `timestamp` (`timestamp`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`images` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#create the contact form
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'contacts';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`contacts` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`phone` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`message` text COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`contacts` ADD PRIMARY KEY (`id`), ADD KEY `timestamp` (`timestamp`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`contacts` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"
fi


#setup the emails to notify table
if [[ `$MY "USE ${SITE_DATABASENAME};SHOW TABLES LIKE 'emails_to_notify';" | tail -n +2 | wc -l` -lt 1 ]];then
  X='CREATE TABLE IF NOT EXISTS `'"${SITE_DATABASENAME}"'`.`contacts` ('
  X=$X'`id` int(10) unsigned NOT NULL,'
  X=$X'`identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X'`email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,'
  X=$X') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`emails_to_notify` ADD PRIMARY KEY (`id`);'
  $MY "$X"

  X='ALTER TABLE `'"${SITE_DATABASENAME}"'`.`emails_to_notify` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
  $MY "$X"

  X='INSERT INTO `'"${SITE_DATABASENAME}"'`.`emails_to_notify` (`identifier`,`email`) VALUES ("contact","'"$COMPANY_SYSADMIN_EMAIL"'")';
  $MY "$X"
fi


echo " * Done setting up the database ${SITE_DATABASENAME}"

exit 0