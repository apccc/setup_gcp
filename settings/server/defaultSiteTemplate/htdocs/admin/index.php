<?php
require_once dirname(dirname(__FILE__)).'/class/zInterface/interfaces/1.php';
require_once dirname(dirname(__FIlE__)).'/class/zInterface/modules/loginModel.php';
$user=loginModel::requireAdmin();
if($user['is_admin']!='T')
	die("You are not authorized to view this area. <a href='/'>Home...</a>");

if(empty($_GET['p'])) $_GET['p']='';
$p=preg_replace('/[^0-9a-zA-Z_.-]/','',$_GET['p']);

$site=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'sites',array(
	'subdomain'=>$_SERVER['SERVER_NAME'],
	'active'=>'T'
));
if(!empty($site))
{
	$section=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'site_sections',array(
		'site_id'=>$site['id'],
		'active'=>'T',
		'identifier'=>'admin'
	));
	if(!empty($section))
	{
		zInterface::setPageCSSIds(explode(',',$section['css_ids']));
		zInterface::setPageJSIds(explode(',',$section['js_ids']));
	}
}
zInterface::setPageTitle((!empty($p)?$p." : ":'').$COMPANY_NAME." Admin");

zInterface::addBotJS(""
	."$('#leftAdminBoxToggle').click(function(){"
		."if($('#leftAdminBox').is(':visible')){"
			."$('#leftAdminBox').hide();"
			."$(this).html('&raquo;');"
			."var h='1';"
		."}else{"
			."$('#leftAdminBox').show();"
			."$(this).html('&laquo;');"
			."var h='2';"
		."}"
		."$.getScript('/admin/h/cookLeftAdminBox.php?h='+h);"
	."});"
);

zInterface::addMidContent(""
."
<div>
<a href='javascript:void(0);' id='leftAdminBoxToggle'>&laquo;</a>
</div>
<table border='0' cellpadding='0' cellspacing='0' width='100%'>
<tr>
<td id='leftAdminBox' valign='top' width='150' "
	."style='padding:10px 30px 10px 0;".(isset($_COOKIE['cookLeftAdminBox'])&&$_COOKIE['cookLeftAdminBox']==1?"display:none;":"")."'"
	.">"
	."<ul>"
		."<li><a href='/admin/'>Admin Home</a></li>"
		."<li><a href='?p=css'>CSS</a></li>"
		."<li><a href='?p=documentation'>Documentation</a></li>"
		."<li><a href='?p=googleCloudStorage_backupSchedule'>GC Backup Schedule</a></li>"
		."<li><a href='?p=googleCloudStorage_buckets'>GC Buckets</a></li>"
		."<li><a href='?p=googleComputeEngine_VMInstances'>GC VMs</a></li>"
		."<li><a href='?p=js'>JS</a></li>"
		."<li><a href='?p=navigation'>Navigation</a></li>"
		."<li><a href='?p=site_pages'>Site Pages</a></li>"
		."<li><a href='?p=site_sections'>Site Sections</a></li>"
		."<li><a href='?p=sites'>Sites</a></li>"
		."<li><a href='?p=users'>Users</a></li>"
		."<li><a href='".$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/'>Home</a></li>"
	."</ul>"
."</td>"
."<td valign='top' style='padding:10px 0;'>"
);

if(isset($p)&&strlen($p)&&is_file(dirname(__FILE__)."/docs/".$p.".php"))
{
	include dirname(__FILE__)."/docs/".$p.".php";
	if($admin_class) zInterface::addMidContent($admin_class->output_content);
}
else
{
	zInterface::addMidContent(""
		."<h1>Admin</h1>"
		."Welcome!"
	);
}
zInterface::addMidContent(""
."</td>
</tr>
</table>
<br style='clear:both;'/>
"
);

echo zInterface::getPage();
?>
