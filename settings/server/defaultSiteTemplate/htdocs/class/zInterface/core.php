<?php
require_once dirname(__FILE__).'/modules/css.php';
require_once dirname(__FILE__).'/modules/js.php';
/**
* Extensible Interface Core Class for building Interfaces
*/
class zInterfaceCore
{

	/**
	* string - The Middle Page Content HTML
	*/
	private static $midContent="";
	/**
	* string - The Head Page Content
	*/
	private static $headContent="";
	/**
	* string - The Page Title
	*/
	private static $pageTitle="";
	/**
	* string - The Page Description
	*/
	private static $pageDescription="";
	/**
	* string - The Page Keywords
	*/
	private static $pageKeywords="";
	/**
	* string - The CSS Ids
	*/
	private static $CSSIds=array();
	/**
	* string - The JS Ids
	*/
	private static $JSIds=array();

	/**
	* Get the Page HTML
	* @return string - Page HTML
	*/
	public static function getPage()
	{
		return ""
			.self::getTop()
			.self::getMid()
			.self::getBot()
		;
	}

	/**
	* Get the Top HTML
	* @return string - Top HTML
	*/
	private static function getTop()
	{
		$header=static::getHeader();
		$CSS_URL=static::getCSS_URL();
		return ""
			."<!DOCTYPE html>"
			."<html>"
			."<head>"
				."<title>".self::$pageTitle."</title>"
				."<meta name='description' content=\"".self::$pageDescription."\" />"
				."<meta name='keywords' content=\"".self::$pageKeywords."\" />"
				.($CSS_URL?"<link rel='stylesheet' type='text/css' href='".$CSS_URL."' />":"")
				.(self::isMobile()?"<meta name='viewport' content='initial-scale=1.0,width=device-width,user-scalable=yes' />":"")
				.self::$headContent
			."</head>"
			."<body>"
			."<div id='wrap'>"
				."<div id='head'>"
					."<div>"
						.$header
					."</div>"
				."</div>"
		;
	}

	/**
	* Get the Middle HTML
	* @return string - Mid HTML
	*/
	private static function getMid()
	{
		return ""
			."<div id='mid'>"
				."<div>"
					.self::$midContent
				."</div>"
			."</div>"
		;
	}

	/**
	* Get the Bottom HTML
	* @return string - Bot HTML
	*/
	private static function getBot()
	{
		return ""
				."<div id='foot'>"
					."<div>"
						.static::getFooter()
					."</div>"
				."</div>"
			."</div>"
			.static::getBotJS()
			."</body>"
			."</html>"
		;
	}

	/**
	* Add Head Content
	* @param string $content - The content to add
	*/
	public static function addHeadContent($content="")
	{
		self::$headContent.=(string)$content;
	}

	/**
	* Add Content to the Middle of the Page
	* @param string $content - The HTML content to add
	*/
	public static function addMidContent($content="")
	{
		self::$midContent.=(string)$content;
	}

	/**
	* Set the Page Title
	* @param string $title - The New Page Title
	*/
	public static function setPageTitle($title)
	{
		self::$pageTitle=(string)$title;
	}

	/**
	* Set the Page Description
	* @param string $description - The New Page Description
	*/
	public static function setPageDescription($description)
	{
		self::$pageDescription=(string)$description;
	}

	/**
	* Set the Page Keywords
	* @param string $keywords - The New Page Keywords
	*/
	public static function setPageKeywords($keywords)
	{
		self::$pageKeywords=(string)$keywords;
	}

	/**
	* Set the Page CSS
	* @param array $CSSIds - The Page CSS Ids
	*/
	public static function setPageCSSIds($CSSIds)
	{
		self::$CSSIds=$CSSIds;
	}

	/**
	* Set the Page JS
	* @param array $JSIds - The PageJS Ids
	*/
	public static function setPageJSIds($JSIds)
	{
		self::$JSIds=$JSIds;
	}

	/**
	* Add a Page CSS Id
	* @param int $CSSId - The CSS Id to add
	*/
	public static function addPageCSSId($CSSId)
	{
		$CSSId=(int)$CSSId;
		if($CSSId)
			if(!in_array($CSSId,self::$CSSIds))
				self::$CSSIds[]=$CSSId;
	}

	/**
	* Add a Page JS Id
	* @param int $JSId - The JS Id to add
	*/
	public static function addPageJSId($JSId)
	{
		$JSId=(int)$JSId;
		if($JSId)
			if(!in_array($JSId,self::$JSIds))
				self::$JSIds[]=$JSId;
	}


	/**
	* Get the CSS File for the interface
	* @return string - the URL of the CSS file
	*/
	protected static function getCSS_URL()
	{
		if(empty(self::$CSSIds)) return false;
		return setup_system_css::getSheetURLFromIds(self::$CSSIds);
	}

	/**
	* Get the JS File for the interface
	* @return string - the URL of the JS file
	*/
	protected static function getJS_URL()
	{
		if(empty(self::$JSIds)) return false;
		return setup_system_js::getScriptURLFromIds(self::$JSIds);
	}

	/**
	* Detect If Is Mobile
	**/
	public static function isMobile()
	{
		return stristr($_SERVER['HTTP_USER_AGENT'],'mobile');
	}
}
?>
