<?php
require_once dirname(__FILE__).'/class/zInterface/interfaces/1.php';
require_once dirname(__FILE__).'/class/zInterface/modules/loginView.php';

zInterface::setPageTitle("Account Created");
zInterface::setPageDescription("Account created.");
zInterface::setPageKeywords("Account created, login");

zInterface::addMidContent(
"
<table border='0' cellpadding='5' cellspacing='0' align='center'>
<tr>
<td valign='top'>
	<div style='".loginView::getLoginBoxStyle()."'>
		Your account was created, and an activation email has been sent to you. Please use the link in the email to activate your account.
		<br/><br/>Thank you!
	</div>
</td>
</tr>
</table>
");

echo zInterface::getPage();
?>