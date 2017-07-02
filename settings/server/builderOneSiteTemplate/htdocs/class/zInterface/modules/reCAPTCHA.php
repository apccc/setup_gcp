<?php
require __DIR__."/curlRequest.php";
/**
* Class for handling of the reCAPTCHA Data
**/
class reCAPTCHA
{
	static $reCaptchaURL='https://www.google.com/recaptcha/api/siteverify';

	/**
	* Verify the captcha
	* @returns bool - whether or not the reCAPTCHA verified
	**/
	public static function verify($response)
	{
		global $RECAPTCHA_SECRET_KEY;
		$ret=curlRequest::post(self::$reCaptchaURL,array(
			'response'=>$response,
			'secret'=>$RECAPTCHA_SECRET_KEY,
			'remoteip'=>$_SERVER['REMOTE_ADDR']
		));
		if(empty($ret)) return false;
		$response=json_decode($ret);
		if(isset($response->success)&&$response->success===true) return $response->success;
		return false;
	}

	/**
	* Get the reCAPTCH challenge HTML from site key
	* @param string $siteKey
	* @returns string - the HTML
	**/
	public static function getChallengeHTML()
	{
		global $RECAPTCHA_SITE_KEY;
		return ""
			."<script src='https://www.google.com/recaptcha/api.js'></script>"
			."<div class='g-recaptcha' data-sitekey='".$RECAPTCHA_SITE_KEY."'></div>"
		;
	}

	
}
?>