<?php
require_once dirname(dirname(__DIR__))."/class/zInterface/modules/loginModel.php";
$user=loginModel::requireAdmin();
// files storage folder
$folder='/red/i/';

$dir = dirname(dirname(__DIR__)).$folder;

if(!is_dir(dirname($dir)))
	throw new Exception("Directory does not exist: ".$dir);

$_FILES['file']['type'] = strtolower($_FILES['file']['type']);

$acceptedTypes=array(
	'image/png'=>'png',
	'image/jpg'=>'jpg',
	'image/gif'=>'gif',
	'image/jpeg'=>'jpg',
	'image/pjpeg'=>'jpg'
);

if(isset($acceptedTypes[$_FILES['file']['type']]))
{
    // setting file's mysterious name
	$filename=preg_replace('/[^0-9a-zA-Z_.-]/','',$_FILES['file']['name']).".".md5(date('YmdHis')).'.'.$acceptedTypes[$_FILES['file']['type']];
    $filePath=$dir.$filename;

    // copying
    copy($_FILES['file']['tmp_name'],$filePath);

    // displaying file
    $array=array(
		'filelink'=>$folder.$filename
    );

    echo stripslashes(json_encode($array));
}
?>