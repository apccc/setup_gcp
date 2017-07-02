<?php
if(!isset($_GET['userid'])||!strlen($_GET['userid'])||!isset($_GET['hash'])||!strlen($_GET['hash']))
	exit;

$resetPasswordPage=true;
require_once dirname(__FILE__).'/class/zInterface/modules/loginModel.php';

$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',(int)$_GET['userid']);
$h=loginModel::getPasswordResetHash((int)$u['id'],$u['email'],$u['nonce']);
if($_GET['hash']!=$h)
	die('Error: 43');

require_once dirname(__FILE__).'/class/zInterface/interfaces/1.php';
require_once dirname(__FILE__).'/class/zInterface/modules/loginView.php';

zInterface::setPageTitle("Reset Password");
zInterface::setPageDescription("Reset your password for your account at.");
zInterface::setPageKeywords("reset, password");


zInterface::addMidContent(
"
<table border='0' cellpadding='5' cellspacing='0' align='center'>
<tr>
<td valgin='top'>
	<div style='".loginView::getLoginBoxStyle()."'>
"
	.loginView::getResetPasswordForm($_GET['userid'],$_GET['hash'])
."
	</div>
</td>
</tr>
</table>
<br style='clear:both;'/>
"
);

echo zInterface::getPage();
?>