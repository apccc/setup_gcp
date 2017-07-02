<?php
interface iCurlRequest
{
	public static function post($url=NULL,$params=array());
	public static function get($url=NULL,$params=array());
}
/**
* Class for performing Curl Requests
*/
class curlRequest implements iCurlRequest
{
	const CURL_USERAGENT="PHP (Curling =)";
	/**
	* Perform a Curl Post Request
	* @param string $url - The destination URL
	* @param array $params - The Post Fields
	* @param bool $includeHeaders - Include the headers or not.
	* @param array $cookies - The Cookies
	* @return string - The response from the URL being posted to
	*/
	public static function post($url=NULL,$params=array(),$includeHeaders=false,$cookies=NULL)
	{
		if(!is_string($url)||!parse_url($url)) return false;
		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//include headers
		if($includeHeaders) curl_setopt($curl,CURLOPT_HEADER,1);
		//set cookies
		if(!empty($cookies)&&is_array($cookies))
			foreach($cookies as $k => $v)
				curl_setopt($curl,CURLOPT_COOKIE,$k.'='.$v);
		//set the connection to post
		curl_setopt($curl,CURLOPT_POST,1);
		//set the params
		if(is_array($params)&&count($params))
			curl_setopt($curl,CURLOPT_POSTFIELDS,$params);
		//set the user agent
		curl_setopt($curl,CURLOPT_USERAGENT,self::CURL_USERAGENT);
		//exec curl
		$rsp=curl_exec($curl);
		curl_close($curl);
		return $rsp;
	}

	/**
	* Perform a Curl Get Request
	* @param string $url - The destination URL
	* @param array $params - The Get Fields
	* @param array $cookies - The Cookies
	* @return string - The response from the URL being requested from
	*/
	public static function get($url=NULL,$params=array(),$includeHeaders=false,$cookies=NULL)
	{
		if(!is_string($url)||!parse_url($url)) return false;
		$uri=$url.(is_array($params)&&count($params)?"?".http_build_query($params):"");
		$curl=curl_init($uri);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//include headers
		if($includeHeaders) curl_setopt($curl,CURLOPT_HEADER,1);
		//set cookies
		if(!empty($cookies)&&is_array($cookies))
			foreach($cookies as $k => $v)
				curl_setopt($curl,CURLOPT_COOKIE,$k.'='.$v);
		//set the user agent
		curl_setopt($curl,CURLOPT_USERAGENT,self::CURL_USERAGENT);
		//exec curl
		$rsp=curl_exec($curl);
		curl_close($curl);
		return $rsp;
	}

	/**
	* Perform a Curl File Download
	* @param string $url - The destination URL
		@param string $destination - The destination Path
	* @param array $params - The Get Fields
	* @param array $cookies - The Cookies
	* @return bool - True if downloaded or false if not
	**/
	public static function download($url=NULL,$destinationPath=NULL,$params=array(),$cookies=NULL)
	{
		if(empty($destinationPath)) return false;
		if(is_file($destinationPath)) unlink($destinationPath);
		$fp = fopen($destinationPath, 'w+');//This is the file where we save the    information
		$uri=$url.(is_array($params)&&count($params)?"?".http_build_query($params):"");
		$curl = curl_init(str_replace(" ","%20",$uri));//Here is the file we are downloading, replace spaces with %20
		curl_setopt($curl, CURLOPT_TIMEOUT, 50);
		//set cookies
		if(!empty($cookies)&&is_array($cookies))
			foreach($cookies as $k => $v)
				curl_setopt($curl,CURLOPT_COOKIE,$k.'='.$v);
		curl_setopt($curl, CURLOPT_FILE, $fp); // write curl response to file
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,true);
		curl_exec($curl); // get curl response
		curl_close($curl);
		fclose($fp);
		if(is_file($destinationPath)) return true;
		return false;
	}

	/**
	* Get Cookies from Result
	* @param string $result - Result from a request
	* @return array - The cookies
	*/
	public static function getCookiesFromResult($result)
	{
		preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $m);
		parse_str($m[1], $cookies);
		return $cookies;
	}

}
?>