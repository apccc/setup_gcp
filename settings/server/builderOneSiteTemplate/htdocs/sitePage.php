<?php
require_once dirname(__FILE__).'/class/objs/database_mysqli/local/local.php';
$site=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'sites',array(
	'subdomain'=>$_SERVER['SERVER_NAME'],
	'active'=>'T'
));
if(empty($site))
{
	header("HTTP/1.0 404 Not Found");
	die("404 Page Not Found!");
}

//look for the section
$section=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_sections',array(
	'active'=>'T',
	'identifier'=>$sectionIdentifier
));

//look for the page, if we have a section
if(!empty($section))
{
	$page=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_pages',array(
		'site_section_id'=>$section['id'],
		'active'=>'T',
		'identifier'=>$pageIdentifier
	));
}

//look for 404 page if no section or page was found
if(empty($section)||empty($page))
{
	$sectionIdentifier='404';
	$pageIdentifier='404';
	$section=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_sections',array(
		'active'=>'T',
		'identifier'=>$sectionIdentifier
	));

	if(!empty($section))
	{
		$page=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_pages',array(
			'site_section_id'=>$section['id'],
			'active'=>'T',
			'identifier'=>$pageIdentifier
		));
	}
}

//give 404 if no section or page was found
if(empty($section)||empty($page))
{
	header("HTTP/1.0 404 Not Found");
	die("404 Page Not Found!");
}

//output the page
require_once __DIR__.'/class/zInterface/interfaces/1.php';
zInterface::setPageCSSIds(explode(',',$section['css_ids']));
zInterface::setPageJSIds(explode(',',$section['js_ids']));
zInterface::setPageTitle($page['title']);
zInterface::setPageDescription($page['description']);
zInterface::setPageKeywords($page['keywords']);
zInterface::addMidContent(
	$page['code_type']=='PHP'?eval($page['code']):$page['code']
);
echo zInterface::getPage();
?>