<?php
require_once dirname(__FILE__).'/class/objs/database_mysqli/local/local.php';
$site=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'sites',array(
	'subdomain'=>$_SERVER['SERVER_NAME'],
	'active'=>'T'
));
if(empty($site)) exit;
$section=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'site_sections',array(
	'site_id'=>$site['id'],
	'active'=>'T',
	'identifier'=>$sectionIdentifier
));
if(empty($section)) exit;
$page=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'site_pages',array(
	'site_section_id'=>$section['id'],
	'active'=>'T',
	'identifier'=>$pageIdentifier
));
if(empty($page)) exit;

require_once dirname(__FILE__).'/class/zInterface/interfaces/1.php';
zInterface::setPageCSSIds(explode(',',$section['css_ids']));
zInterface::setPageJSIds(explode(',',$section['js_ids']));
zInterface::setPageTitle($page['title']);
zInterface::setPageDescription($page['description']);
zInterface::setPageKeywords($page['keywords']);
zInterface::addMidContent(
	substr_compare($page['identifier'],'php_',0,4)===0?eval($page['code']):$page['code']
);
echo zInterface::getPage();
?>