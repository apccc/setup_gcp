<?php
header("Content-Type: text/css",true);
header("X-Content-Type-Options: nosniff");
header('Pragma: public');
header('Cache-Control: max-age=86400');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 60 * 24) . ' GMT');
if(empty($_GET['q'])) exit;
$_GET['q']=preg_replace('[^0-9-]','',$_GET['q']);
$ids=explode('-',$_GET['q']);
foreach($ids as $key=>$id) if(!$id||!is_numeric($id)||substr($id,0,1)=='0') unset($ids[$key]);
if(!count($ids)) exit;

require_once dirname(__FILE__).'/class/objs/database_mysqli/local/local.php';
$sql="SELECT `whitelisted_ips`,`sheet` FROM `".$SITE_DATABASE."`.`css` WHERE `id`IN(".$database_mysqli_local->mysqlidb->real_escape_string(implode(',',$ids)).") ORDER BY `ord` ASC";
foreach($database_mysqli_local->mysqlidb->getRowsFromQuery($sql) as $r)
{
	if(isset($r['whitelisted_ips'])&&strlen($r['whitelisted_ips'])&&!strstr($r['whitelisted_ips'],$_SERVER['REMOTE_ADDR'])) continue;//bypass ips not whitelisted, when using this feature
	echo $r['sheet'];
}
?>