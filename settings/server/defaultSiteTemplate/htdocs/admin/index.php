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

$pNav=array(
	'css'=>'CSS',
	'database_tables'=>'Database Tables',
	'database_table_fields'=>'Database Table Fields',
	'documentation'=>'Documentation',
	'googleCloudStorage_backupSchedule'=>'GC Backup Schedule',
	'googleCloudStorage_buckets'=>'GC Buckets',
	'googleComputeEngine_VMInstances'=>'GC VMs',
	'js'=>'JS',
	'navigation'=>'Navigation',
	'site_pages'=>'Site Pages',
	'site_sections'=>'Site Sections',
	'sites'=>'Sites',
	'sites_vms'=>'Sites VMS',
	'users'=>'Users'
);
$sql="SELECT * FROM `".$SYSTEM_DATABASE."`.`database_tables` WHERE `active`='T' AND `controls`='T'";
$pNavDatabaseTables=$database_mysqli_local->mysqlidb->getRowsFromQuery($sql);
foreach($pNavDatabaseTables as $x)
	$pNav[$x['id']]=$x['database']." : ".$x['table'];
asort($pNav);
$pNavStr='';
foreach($pNav as $k=>$v)
	$pNavStr.="<li><a href=\"?p=".$k."\">".$v."</a></li>";

zInterface::addMidContent(""
."
<div>
<a href='javascript:void(0);' id='leftAdminBoxToggle'>&laquo;</a>
</div>
<table id='adminBox' border='0' cellpadding='0' cellspacing='0' width='100%'>
<tr>
<td id='leftAdminBox' valign='top' width='150' "
	."style='padding:10px 30px 10px 0;".(isset($_COOKIE['cookLeftAdminBox'])&&$_COOKIE['cookLeftAdminBox']==1?"display:none;":"")."'"
	.">"
	."<ul>"
		."<li><a href='/admin/'>Admin Home</a></li>"
		.$pNavStr
		."<li><a href='".$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/'>Home</a></li>"
	."</ul>"
."</td>"
."<td id='rightAdminBox' valign='top' style='padding:10px 0;'>"
);

if(isset($p)&&strlen($p))
{
	if(is_file(__DIR__."/docs/".$p.".php"))
		include __DIR__."/docs/".$p.".php";
	elseif(is_numeric($p))
		include __DIR__."/docs/server.database.table.php";
	else
		throw new Exception("Bad p parameter");
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
