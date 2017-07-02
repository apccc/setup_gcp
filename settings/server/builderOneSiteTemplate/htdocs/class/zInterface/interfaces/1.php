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
		global $SITE_DATABASE;
		global $database_mysqli_local;
		$section=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_sections',array('identifier'=>'head','active'=>'T'));
		if(empty($section)) return "";
		$page=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_pages',array('site_section_id'=>$section['id'],'identifier'=>'head','active'=>'T'));
		if(empty($page)) return "";
		if(!empty($section['css_ids']))
			foreach(explode(',',$section['css_ids']) as $id)
				self::addPageCSSId(trim($id));
		if(!empty($section['js_ids']))
			foreach(explode(',',$section['js_ids']) as $id)
				self::addPageJSId(trim($id));
		return ""
			.($page['code_type']=='PHP'?eval($page['code']):$page['code'])
		;
	}

	/**
	* Get the Footer
	* @return string - Return the footer HTML for the core
	*/
	protected static function getFooter()
	{
		$out="";
		global $SITE_DATABASE;
		global $database_mysqli_local;
		$section=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_sections',array('identifier'=>'foot','active'=>'T'));
		if(empty($section)) return "";
		$page=$database_mysqli_local->mysqlidb->getRow($SITE_DATABASE,'site_pages',array('site_section_id'=>$section['id'],'identifier'=>'foot','active'=>'T'));
		if(empty($page)) return "";
		if(!empty($section['css_ids']))
			foreach(explode(',',$section['css_ids']) as $id)
				self::addPageCSSId(trim($id));
		if(!empty($section['js_ids']))
			foreach(explode(',',$section['js_ids']) as $id)
				self::addPageJSId(trim($id));

		//PUT IN THE BOTTOM
		$out.=""
			.($page['code_type']=='PHP'?eval($page['code']):$page['code'])
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