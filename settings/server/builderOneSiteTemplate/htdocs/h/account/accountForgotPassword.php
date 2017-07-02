<?php
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/sendEmail.php';
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/loginModel.php';

$message="";
if(!isset($_POST['email'])||!strlen($_POST['email']))
	$message.="Please enter your email, and we will email you a password reset link.";

if(!strlen($message)&&$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',array(
	'email'=>$_POST['email']
)))
{
	sendEmail::text(
		stripslashes($_POST['email']),
		"Account Password Reset Link",
		"A request was made to reset the password for the user account at this email address.\n"
		."Please use the following link to reset your password: \n\n"
		.$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/resetPassword/".$u['id']."/".loginModel::getPasswordResetHash($u['id'],$u['email'],$u['nonce'])."\n\n"
		."-- ".$COMPANY_NAME
		,
		"noreply@".$SITE_CONTROL_DOMAIN
	);
	$message.="We've sent a password reset link to your email address. Use the link there to reset your password.";
}
elseif(!strlen($message))
{
	$message.="No account was found with that email address.";
}

if(strlen($message))
{
	echo "alert(\"".$message."\");";
	exit;
}
?>