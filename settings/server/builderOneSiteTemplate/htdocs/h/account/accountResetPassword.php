<?php
if(!isset($_POST['userid'])||!strlen($_POST['userid'])||!isset($_POST['hash'])||!strlen($_POST['hash']))
	die("alert('Error: 4');");

require_once dirname(dirname(__DIR__)).'/class/zInterface/modules/loginModel.php';

$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',(int)$_POST['userid']);
$h=loginModel::getPasswordResetHash((int)$_POST['userid'],$u['email'],$u['nonce']);

$message="";

if($_POST['hash']!=$h)
	$message.="Error: 93-";

if($_POST['password']!=$_POST['passwordConfirm'])
	$message.="Your passwords do not match!";


if(!$message)
{
	if($database_mysqli_local->mysqlidb->updateRows($SYSTEM_DATABASE,'users',array(
		'password'=>loginModel::getEncryptedPassword($_POST['password'],$u['nonce'])
	),$u['id']))
	{
		$message.="Your password was updated.";
	}
	else
	{
		$message.="There was an error processing your request. Please try again!";
	}
}

if(strlen($message))
	die("alert(\"".$message."\");");
?>