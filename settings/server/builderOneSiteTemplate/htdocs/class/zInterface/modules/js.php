<?php
require_once dirname(dirname(dirname(__FILE__))).'/objs/database_mysqli/local/local.php';

/**
* Class for the JS
**/
class setup_system_js
{
	/**
	* Get Sheet URL from Ids
	* @param array $ids - the array of ids
	* @returns string the URL for the sheet
	**/
	public static function getScriptURLFromIds($ids)
	{
		if(!(isset($ids)&&is_array($ids)&&!empty($ids))) return false;

		foreach($ids as $k=>$v)
			if(!$v)
				unset($ids[$k]);

		$idsString=implode(',',$ids);
		if(!strlen($idsString)) return false;

		global $database_mysqli_local;
		global $SITE_DATABASE;
		global $SITE_CONTROL_DOMAIN;
		global $SITE_PROTOCOL;

		$sql=""
			."SELECT `lastUpdated` "
			."FROM `".$SITE_DATABASE."`.`js` "
			."WHERE `id`IN(".$database_mysqli_local->mysqlidb->real_escape_string($idsString).") "
			."ORDER BY `lastUpdated` DESC "
			."LIMIT 1"
		;
		$r=$database_mysqli_local->mysqlidb->getRowFromQuery($sql);

		$x=preg_replace('/[^0-9]/','',$r['lastUpdated']);
		return $SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/js/script0".$x."-".str_replace(',','-',$idsString).".js";
	}

	/**
	* Get the JS Script Tag from ids
	**/
	public static function getJSScriptTagFromIds($ids=false)
	{
		$x=self::getScriptURLFromIds($ids);
		if(!$x) return false;
		return "<script src='".$x."'></script>";
	}
}
?>