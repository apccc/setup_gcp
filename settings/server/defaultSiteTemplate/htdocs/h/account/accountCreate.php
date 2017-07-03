<?php
require_once dirname(dirname(dirname(__FILE__))).'/class/objs/database_mysqli/local/local.php';
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/loginView.php';
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/sendEmail.php';
require_once dirname(dirname(dirname(__FILE__))).'/class/zInterface/modules/reCAPTCHA.php';

$message="";
$fields=array(
	'first_name'=>'First Name',
	'last_name'=>'Last Name',
	'email'=>'Email',
	'emailConfirm'=>'Email Confirmation',
	'password'=>'Password',
	'passwordConfirm'=>'Password Confirmation'
);
foreach($fields as $k=>$v)
	if(!isset($_POST[$k])||!strlen($_POST[$k]))
		$message.=$v." Required!\\n";

if($_POST['email']!=$_POST['emailConfirm'])
	$message.="Email and Email Confirmation do not match!\\n";

if($_POST['password']!=$_POST['passwordConfirm'])
	$message.="Password and Password Confirmation do not match!\\n";

if(isset($RECAPTCHA_SITE_KEY)&&$RECAPTCHA_SITE_KEY&&isset($RECAPTCHA_SITE_KEY)&&isset($RECAPTCHA_SECRET_KEY))
	if(empty($_POST['recap'])||!$reCAPTCHAReply=reCAPTCHA::verify($_POST['recap']))
		$message.="ReCAPTCHA Error. Please try again!\\n";

if(!$message)
{
	$user=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',array(
		'email'=>$_POST['email']
	));
	if(!empty($user['id']))
		$message.="An account already exists with this email!\\n";
}

if(strlen($message))
{
	$message="Please correct the following:\\n\\n".$message;
	echo ""
		."alert(\"".$message."\");"
		.(isset($RECAPTCHA_SITE_KEY)&&$RECAPTCHA_SITE_KEY&&isset($RECAPTCHA_SITE_KEY)&&isset($RECAPTCHA_SECRET_KEY)?""
			."if(window.grecaptcha) grecaptcha.reset();"
			:"")
	;
	exit;
}

//NO ERRORS LET'S PROCEED
$nonce=exec('tr -cd [:alnum:] < /dev/urandom | head -c 250');
if($uid=$database_mysqli_local->mysqlidb->insertInto($SYSTEM_DATABASE,'users',array(
	'first_name'=>$_POST['first_name'],
	'last_name'=>$_POST['last_name'],
	'email'=>$_POST['email'],
	'password'=>loginModel::getEncryptedPassword($_POST['password'],$nonce),
	'nonce'=>$nonce,
	'url'=>urldecode($_POST['url'])
)))
{
	if(!sendEmail::text(
		stripslashes($_POST['email']),
		$COMPANY_NAME." Account Activation Link",
		"A new account was created with this email address.\n"
		."Please use the following link to activate your account: \n\n"
		.$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/accountActivate/".$uid."/".loginModel::getPasswordResetHash($uid,stripslashes($_POST['email']),$nonce)."\n\n"
		."-- ".$COMPANY_NAME
		,
		"noreply@".$SITE_CONTROL_DOMAIN
	))
		echo "alert('Activation email could not be sent...');";

	//account created
	echo "document.location='/accountCreated';";
	exit;
}
else
{
	//account not created
	echo "alert('There was an error, your account was not created. Please try again!');";
	exit;
}

?>