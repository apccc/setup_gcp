<?php
if(!isset($_GET['userid'])||!strlen($_GET['userid'])||!isset($_GET['hash'])||!strlen($_GET['hash']))
	die('1');

require_once dirname(__FILE__).'/class/zInterface/modules/loginModel.php';
require_once dirname(__FILE__).'/class/objs/database_mysqli/local/local.php';
$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',(int)$_GET['userid']);
if(empty($u['id']))
	die('2');
$h=loginModel::getPasswordResetHash((int)$u['id'],$u['email'],$u['nonce']);
if($_GET['hash']!=$h)
	die('3');

if($u['active']!='T')
	$database_mysqli_local->mysqlidb->updateRows($SYSTEM_DATABASE,'users',array(
		'active'=>'T'
	),$u['id']);

loginModel::setLoginCookies($u['id'],$u['email'],$u['nonce']);

if(isset($u['url'])&&strlen($u['url']))
{
	header("Location: ".$u['url']);
	exit;
}

require_once dirname(__FILE__).'/class/zInterface/interfaces/1.php';
require_once dirname(__FILE__).'/class/zInterface/modules/loginView.php';

zInterface::setPageTitle("Account Activated");
zInterface::setPageDescription("account activated.");
zInterface::setPageKeywords("login");

zInterface::addMidContent(""
	."<table border='0' cellpadding='5' cellspacing='0' align='center'>"
		."<tr>"
			."<td valign='top'>"
				."<div style='".loginView::getLoginBoxStyle()."'>"
					."Your account was activated."
					."<br/><br/>Thank you!"
					."<br/><br/>-- "
					.(!empty($u['url'])?""
						."<br/><br/><br/>"
						."<a href='".$u['url']."'>Continue &raquo;</a>"
					:"")
				."</div>"
			."</td>"
		."</tr>"
	."</table>"
);

echo zInterface::getPage();
?>