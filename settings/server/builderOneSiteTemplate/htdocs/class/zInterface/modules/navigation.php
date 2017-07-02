<?php
class navigation
{
	/**
	* Get the Navigation HTML
	* @param string $identifier - The navigation identifier
	* @returns string - The HTML for the navigation
	**/
	public static function getNavigationHTML($identifier)
	{
		if(empty($identifier)) return "";
		if(strstr($_SERVER['REQUEST_URI'],'?'))
			list($currentHREF,$x)=explode('?',$_SERVER['REQUEST_URI'],2);
		else
			$currentHREF=$_SERVER['REQUEST_URI'];

		return self::getNavigationHTMLFromNavigationArray(self::getActiveNavigation($identifier),$identifier,$currentHREF);
	}

	/**
	* Get the Navigation from Navigation Array
	* @param array $navigationArray - The navigation array of rows from the database
	* @param string $identifier - The navigation identifier
	* @param string $currentHREF - The current href URL to show which item is active
	* @returns string - The HTML for the navigation
	**/
	private static function getNavigationHTMLFromNavigationArray($navigationArray,$identifier,$currentHREF=NULL)
	{
		$out="";
		if(empty($navigationArray)) return $out;
		$parent=$navigationArray[0]['parent'];
		$out.=""
			.(!$parent?"<nav class='sitenav'>":"")
				."<ul>"
		;
		foreach($navigationArray as $nav)
			$out.=""
				."<li>"
					."<a href='".$nav['href']."'".(!empty($currentHREF)&&$nav['href']==$currentHREF?" class='a'":"").">".$nav['content']."</a>"
					.self::getNavigationHTMLFromNavigationArray(self::getActiveNavigation($identifier,$nav['id']),$currentHREF)
				."</li>"
			;
		$out.=""
				."</ul>"
			.(!$parent?"</nav>":"")
		;
		return $out;
	}

	/**
	* Get the Active Navigation
	* @param str $identifier - The navigation identifier
	* @param str $parent - optional - Set the parent requesting navigation items
	* @returns array - The array of active navigation
	**/
	private static function getActiveNavigation($identifier,$parent='0')
	{
		global $database_mysqli_local;
		global $SITE_DATABASE;

		//get static navigation
		$sql="SELECT * FROM `".$SITE_DATABASE."`.`navigation` WHERE `active`='T' AND `parent`='".(int)$parent."' AND `id`!='".(int)$parent."' ORDER BY `ord` ASC";
		$array=$database_mysqli_local->mysqlidb->getRowsFromQuery($sql);

		//future development - to do - get dynamic navigation

		return $array;
	}
}
?>