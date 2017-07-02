<?php
require_once dirname(dirname(__DIR__))."/class/zInterface/modules/loginModel.php";
$user=loginModel::requireAdmin();
// files storage folder
$folder='/red/f/';

$dir = dirname(dirname(__DIR__)).$folder;

if(!is_dir(dirname($dir)))
	throw new Exception("Directory does not exist: ".$dir);

// setting file's mysterious name
$filename=preg_replace('/[^0-9a-zA-Z_.-]/','',$_FILES['file']['name']);
$filePath=$dir.$filename;

$uploaded=@move_uploaded_file($_FILES['file']['tmp_name'],$filePath);

if($uploaded&&is_file($filePath))
{
	$array = array(
		'filelink' => $folder.$filename,
		'filename' => $filename
	);

	echo stripslashes(json_encode($array));
}
?>