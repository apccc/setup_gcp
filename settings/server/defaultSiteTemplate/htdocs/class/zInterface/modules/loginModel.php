<?php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/settings.php';
require_once dirname(dirname(dirname(__FILE__))).'/objs/database_mysqli/local/local.php';

class loginModel
{
	public static $u=NULL; //USER STORAGE
	public static $whiteIp=false; //WHETHER THE IP IS WhiteListed

	public static function getHash($userid=NULL,$email=NULL,$nonce=NULL)
	{
		if(is_numeric($userid)&&is_string($email)&&is_string($nonce))
			return hash('sha512',$userid.$email.$nonce);
	}

	public function getEncryptedPassword($password=NULL,$nonce=NULL)
	{
		if(is_string($password)&&is_string($nonce))
			return hash('sha512',$password.$nonce);
	}

	public static function setLoginCookies($userid=NULL,$email=NULL,$nonce=NULL)
	{
		if(is_numeric($userid)&&is_string($email)&&strlen($email)&&strlen($nonce))
		{
			$hash=self::getHash($userid,$email,$nonce);
			setcookie(self::getCookiePrefix().'email',stripslashes($email),time()+60*60*24*30,'/',self::getCookieDomain());
			setcookie(self::getCookiePrefix().'hash',$hash,time()+60*60*24*30,'/',self::getCookieDomain());
			$_COOKIE[self::getCookiePrefix().'email']=stripslashes($email);
			$_COOKIE[self::getCookiePrefix().'hash']=$hash;
		}
	}

	public static function getPasswordResetHash($userid=NULL,$email=NULL,$nonce=NULL)
	{
		if(is_numeric($userid)&&is_string($email)&&is_string($nonce))
			return hash('sha512',$email.$nonce.$userid);
	}

	public static function unsetLogin()
	{
		setcookie(self::getCookiePrefix().'email','',time()-60*60*24*30,'/',self::getCookieDomain());
		setcookie(self::getCookiePrefix().'hash','',time()-60*60*24*30,'/',self::getCookieDomain());
		$_COOKIE[self::getCookiePrefix().'email']=NULL;
		$_COOKIE[self::getCookiePrefix().'hash']=NULL;
	}

	public static function getCookiePrefix()
	{
		global $COMPANY_DOMAIN;
		return str_replace('.','_',$COMPANY_DOMAIN);
	}

	public static function getCookieDomain()
	{
		global $COMPANY_DOMAIN;
		return $COMPANY_DOMAIN;
	}

	public static function getLoggedInUser()
	{
		if(isset(static::$u)&&is_array(static::$u))
			return static::$u;
		if(isset($_COOKIE[self::getCookiePrefix().'email'])&&isset($_COOKIE[self::getCookiePrefix().'hash'])&&strlen($_COOKIE[self::getCookiePrefix().'email'])&&strlen($_COOKIE[self::getCookiePrefix().'hash']))
		{
			global $database_mysqli_local;
			global $SYSTEM_DATABASE;
			$u=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'users',array(
					'email'=>$_COOKIE[self::getCookiePrefix().'email'],
					'active'=>'T',
				));
			if(!empty($u['id']))
			{
				if(
					$_COOKIE[self::getCookiePrefix().'email']==stripslashes($u['email'])&&
					$_COOKIE[self::getCookiePrefix().'hash']==self::getHash($u['id'],$u['email'],$u['nonce'])
				)
				{
					static::$u=$u;
					return $u;
				}
			}
		}
		return false;
	}

	public static function requireLoggedInUser()
	{
		global $SITE_PROTOCOL;
		global $SITE_CONTROL_DOMAIN;
		if($u=self::getLoggedInUser())
		{
			return $u;
		}
		else
		{
			header("Location: ".$SITE_PROTOCOL."://".$SITE_CONTROL_DOMAIN."/log?url=".urlencode($SITE_PROTOCOL."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']));
			exit;
		}
	}

	public static function checkIPForWhitelisting()
	{
		if(!self::$whiteIp)
		{
			global $SYSTEM_DATABASE;
			global $database_mysqli_local;
			$whitelistedIp=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'whitelisted_ips',array(
				'ip'=>$_SERVER['REMOTE_ADDR']
			));
			if(!empty($whitelistedIp['id']))
				self::$whiteIp=true;
		}
		return self::$whiteIp;
	}

	public static function requireAdmin()
	{
		$u=self::requireLoggedInUser();
		if(empty($u)||$u['is_admin']!='T')
			return false;

		if(!self::checkIPForWhitelisting())
			die('Not Authorized.');

		return $u;
	}

	/**
	* Get the Password MySQL Value
	* @param str $string - The password input
	* @param str $nonce - The user nonce
	* @returns str - The string for the mysql command
	*/
	public static function getSQLPasswordValueFromString($string,$nonce)
	{
		return "'".self::getEncryptedPassword($string,$nonce)."'";
	}
}

?>
