<?php

require_once dirname(dirname(__FILE__)).'/settings.php';
require_once dirname(__FILE__).'/class/zInterface/interfaces/1.php';
require_once dirname(__FILE__).'/class/zInterface/modules/loginView.php';

if(isset($_GET['logout']))
	loginModel::unsetLogin();

zInterface::setPageTitle("Login - ".$COMPANY_NAME);
zInterface::setPageDescription("Login to ".$COMPANY_NAME." using your account information.");
zInterface::setPageKeywords("login");

zInterface::addMidContent(
"
<div align='center' style='vertical-align:top;'>
<table border='0' cellpadding='5' cellspacing='0' align='center' style='display:inline-block;vertical-align:top;'>
<tr>
<td valign='top'>
<h1>Login</h1>
	<div style='".loginView::getLoginBoxStyle()."'>
"
	.loginView::getLoginForm()
."
	</div>
</td>
</tr></table>
<table border='0' cellpadding='5' cellspacing='0' align='center' style='display:inline-block;'>
<tr>
<td valgin='top'>
<h1>Signup</h1>
	<div style='".loginView::getLoginBoxStyle()."'>
"
	.loginView::getSignupForm()
."
	</div>
</td>
</tr>
</table>
</div>
<br style='clear:both;'/>
"
);
echo zInterface::getPage();
?>