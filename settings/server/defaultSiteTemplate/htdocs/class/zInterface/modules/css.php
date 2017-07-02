<?php
require_once dirname(dirname(dirname(__FILE__))).'/objs/database_mysqli/local/local.php';

/**
* Class for the CSS
**/
class setup_system_css
{
	/**
	* Get Sheet URL from Ids
	* @param array $ids - the array of ids
	* @returns string the URL for the sheet
	**/
	public static function getSheetURLFromIds($ids)
	{
		if(!(isset($ids)&&is_array($ids)&&!empty($ids))) return false;

		$idsString=implode(',',$ids);
		if(!strlen($idsString)) return false;

		global $database_mysqli_local;
		global $SYSTEM_DATABASE;
		global $SITE_CONTROL_DOMAIN;
		global $SITE_PROTOCOL;

		$sql=""
			."SELECT `lastUpdated` "
			."FROM `".$SYSTEM_DATABASE."`.`css` "
			."WHERE `id`IN(".$database_mysqli_local->mysqlidb->real_escape_string($idsString).") "
			."ORDER BY `lastUpdated` DESC "
			."LIMIT 1"
		;
		$r=$database_mysqli_local->mysqlidb->getRowFromQuery($sql);

		$x=preg_replace('/[^0-9]/','',$r['lastUpdated']);
		return $SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/css/style0".$x."-".str_replace(',','-',$idsString).".css";
	}

	/**
	* Get the CSS Link
	**/
	public static function getCSSLinkFromIds($ids=false)
	{
		$x=self::getSheetURLFromIds($ids);
		if(!$x) return false;
		return "<link rel='stylesheet' type='text/css' href='".$x."' />";
	}
}
?>