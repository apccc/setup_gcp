<?php
require_once dirname(__FILE__).'/class/objs/database_mysqli/local/local.php';
if(empty($_GET['f'])||empty($_GET['t'])||empty($_GET['id'])) exit;
$r=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,$_GET['t'],(int)$_GET['id']);
if(empty($r[$_GET['f']])) exit;
$f=$r[$_GET['f']];
$fileData = base64_decode($f);
$fo=finfo_open();
$mime_type=finfo_buffer($fo,$fileData,FILEINFO_MIME_TYPE);
if(in_array($mime_type,array('image/png','image/gif','image/jpg','image/jpeg')))
{
	header('Pragma: public');
	header('Cache-Control: max-age=3600');
	header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
}
header('Content-Type:'.$mime_type);
echo $fileData;
?>