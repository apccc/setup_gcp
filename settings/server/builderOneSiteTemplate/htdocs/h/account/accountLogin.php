<?php
require_once dirname(dirname(dirname(dirname(__FILE__))))."/settings.php";
require_once dirname(dirname(dirname(__FILE__))).'/class/objs/database_mysqli/local/local.php';
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/loginModel.php';

$message="";
$redirectURL="";

//field check
if(!isset($_POST['email'])||!strlen($_POST['email']))
	$message="Please enter your email address.";
elseif(!isset($_POST['password'])||!strlen($_POST['password']))
	$message="Please enter your password.";


//clear old attempts
$sql="DELETE FROM `".$SYSTEM_DATABASE."`.`login_tries` WHERE `timestamp`<=DATE_SUB(NOW(),INTERVAL 2 DAY)";
$x=$database_mysqli_local->mysqlidb->query($sql);
//check for attempts
$x=$database_mysqli_local->mysqlidb->getRows($SYSTEM_DATABASE,'login_tries',array(
	'ip'=>$_SERVER['REMOTE_ADDR']
));
if(!empty($x)&&count($x)>10)
	$message="You have too many login attempts. Please wait.";

if(!strlen($message))
{
	$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',array(
		'email'=>$_POST['email']
	));
	$pw=trim(loginModel::getSQLPasswordValueFromString($_POST['password'],$u['nonce']),"'");
	if($pw!=$u['password'])
		unset($u);
}

if(!strlen($message)&&!empty($u['id']))
{
	loginModel::setLoginCookies($u['id'],$u['email'],$u['nonce']);
	if(isset($_POST['url'])&&strlen($_POST['url']))
	{
		echo "document.location='".urldecode($_POST['url'])."';";
		exit;
	}
	$message.="You're logged in!";
}
elseif(!strlen($message))
{
	$x=$database_mysqli_local->mysqlidb->insertInto($SYSTEM_DATABASE,'login_tries',array(
		'email'=>$_POST['email'],
		'ip'=>$_SERVER['REMOTE_ADDR']
	));
	$message="Your account information could not be authenticated, please try again.";
}
if($message)
	echo ""
		."alert(\"".$message."\");"
	;
?>