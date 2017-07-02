<?php
require_once dirname(dirname(__FILE__)).'/core.php';
require_once dirname(dirname(__FILE__)).'/modules/loginModel.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/settings.php';
require_once dirname(dirname(dirname(__FILE__))).'/objs/database_mysqli/local/local.php';;

/**
* Interface
*/
class zInterface extends zInterfaceCore
{
	/**
	* Bottom JS
	*/
	private static $botJS="";

	/**
	* Get the Header
	* @return string - Return the header HTML for the core
	*/
	protected static function getHeader()
	{
		global $SITE_PROTOCOL;
		global $SITE_CONTROL_DOMAIN;
		global $COMPANY_NAME;
		global $SYSTEM_DATABASE;
		global $database_mysqli_local;
		$user=loginModel::getLoggedInUser();
		$site=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'sites',array(
			'subdomain'=>$SITE_CONTROL_DOMAIN
		));
		if(isset($site['logo'])&&strlen($site['logo']))
		{
			$fileData=base64_decode($site['logo']);
			$fo=finfo_open();
			$mime_type=finfo_buffer($fo,$fileData,FILEINFO_MIME_TYPE);
			$siteLogoCode="data:".$mime_type.";base64,".$site['logo'];
			if($mime_type=='image/png')
				$logoExt='png';
			else
				$logoExt='png';

			$siteLogoURL=$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/afile/sites/".$site['id'].".logo.".$logoExt;
		}
		return ""
			."<div id='upperHead'>"
				."<div>"
					."<div style='float:right'>"
						.(!empty($user)
							?$user['first_name']." ".$user['last_name']." &nbsp; &nbsp; <a href='".$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/log?logout'>sign out</a>"
							:"<a href='".$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/log?url=".urlencode($SITE_PROTOCOL."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'])."'>sign in</a>"
						)
					."</div>"
					."<a href='/'>"
						.(isset($siteLogoURL)&&$siteLogoURL?""
							."<img alt=\"".$COMPANY_NAME."\" src='".$siteLogoURL."' />"
							:$COMPANY_NAME
						)
					."</a>"
				."</div>"
			."</div>"
			."<div id='lowerHead'>"
				."<div>"
				."</div>"
			."</div>"
		;
	}

	/**
	* Get the Footer
	* @return string - Return the footer HTML for the core
	*/
	protected static function getFooter()
	{
		global $COMPANY_NAME;

		$out="";

		//GET THE USER INFO
		$user=loginModel::getLoggedInUser();

		//PUT IN THE COPYRIGHT INFO
		$out.=""
			."<div>"
			."&copy; ".date('Y')." ".$COMPANY_NAME
			."</div>"
		;

		return $out;
	}

	/**
	* Get the Bot JS
	* @return string - Return the Bottom JS
	*/
	protected static function getBotJS()
	{
		$pageJSURL=self::getJS_URL();
		$documentReadyJS=""
			.(isset($pageJSURL)&&$pageJSURL?"$.getScript('".$pageJSURL."');":"")
			.(string)self::$botJS
		;
		return ""
			.(strlen($documentReadyJS)?""
				."<script src='//ajax.googleapis.com/ajax/libs/jquery/"
					."1.8.2"
					."/jquery.min.js'></script>"
				."<script>"
					."$(document).ready(function(){"
						.$documentReadyJS
					."});"
				."</script>"
				:""
			)
		;
	}

	/**
	* Add to the Bot JS
	* @param string $code - The Code to add to the Bot JS
	*/
	public static function addBotJS($code)
	{
		self::$botJS.=$code;
	}
}
?>