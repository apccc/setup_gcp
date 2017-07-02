<?php
require_once __DIR__.'/sendEmail.php';
/**
* Contact Form Class
*/
class contactForm
{
	const captcha=true;

	/**
	* @returns string
	**/
	public static function get()
	{
		if(self::captcha)
			require_once dirname(__FILE__).'/reCAPTCHA.php';

		if(isset($_POST['submit'])&&strlen($_POST['submit'])&&isset($_POST['g-recaptcha-response'])&&self::captcha)
			$captchaVerify=reCAPTCHA::verify($_POST['g-recaptcha-response']);
		elseif(!self::captcha)
			$captchaVerify=true;

		if(
			isset($_POST["submit"])&&strlen($_POST["submit"])&&
			isset($_POST["first_name"])&&strlen($_POST["first_name"])&&
			isset($_POST["last_name"])&&strlen($_POST["last_name"])&&
			isset($_POST["email"])&&strlen($_POST["email"])&&
			isset($_POST["phone"])&&strlen($_POST["phone"])&&
			isset($_POST["message"])&&strlen($_POST["message"])&&
			$captchaVerify
		)
		{
			self::saveContact();
			self::sendEmail();
			return self::getThankYouHTML();
		}
		else
		{
			$message=(isset($captchaVerify)&&$captchaVerify===false?"<li>Your CAPTCHA Code Was Not Verified. Please Try Again!</li>":"");
			if(!empty($_POST["submit"]))
				foreach(array('first_name','last_name','email','phone','message') as $check)
					if(empty($_POST[$check]))
						$message.="<li>Please Fill Out The ".ucwords(str_replace('_',' ',$check))." Field!</li>";
			if(!empty($message))
				$message="<ul>".$message."</ul>";
			return self::getFormHTML($message);
		}
	}

	/**
	* Show the Thank you message
	* @returns string
	*/
	private static function getThankYouHTML()
	{
		global $COMPANY_DOMAIN;
		return ""
			."<div id='contactFormThankYou'>"
				."<div class='a'>Thank You...</div>"
				."<div class='b'>We will contact you soon!</div>"
				."<span id='contactFormSignature'>".$COMPANY_DOMAIN."</font>"
			."</div>"
		;
	}

	/**
	* Send the Contact Notice
	*/
	private static function sendEmail()
	{
		$message=""
			.stripslashes($_POST['first_name']." ".$_POST['last_name'])." sent a Contact Form Submission\n\n"
			."Email:\n".stripslashes($_POST['email'])."\n\n"
			."Phone:\n".stripslashes($_POST['phone'])."\n\n"
			."Message:\n".stripslashes($_POST['message'])."\n\n\n\n"
			."-- Your Friendly Automated Notifier\n"
		;
		global $database_mysqli_local;
		global $SITE_DATABASE;
		global $COMPANY_DOMAIN;
		foreach($database_mysqli_local->mysqlidb->getRows($SITE_DATABASE,'emails_to_notify',array(
			'identifier'=>'contact'
		)) as $contact)
			sendEmail::text($contact['email'],$COMPANY_DOMAIN." Contact Form Submission",$message);
	}

	/**
	* Get the Form HTML
	* @param string $message - The Message To Output to the User
	*/
	private static function getFormHTML($message=NULL)
	{
		$out="";
		$out.=""
			.(isset($message)&&$message?"<div id='contactFormErrorMessage'>".$message."</div>":"")
			."<form action='' method='post' id='contactmain' name='contactmain'>"
				."<table border='0' cellspacing='0' cellpadding='0' width='100%'>"
					."<tbody>"
						."<tr>"
							."<td><input type='text' placeholder='First Name *' name='first_name' class='contactinput' value=\"".(isset($_POST['first_name'])?htmlspecialchars(stripslashes($_POST['first_name'])):"")."\" /></td>"
						."</tr>"
						."<tr>"
							."<td><input type='text' placeholder='Last Name *' name='last_name' class='contactinput' value=\"".(isset($_POST['last_name'])?htmlspecialchars(stripslashes($_POST['last_name'])):"")."\" /></td>"
						."</tr>"
						."<tr>"
							."<td><input type='text' placeholder='Email *' name='email' class='contactinput' value=\"".(isset($_POST['email'])?htmlspecialchars(stripslashes($_POST['email'])):"")."\" /></td>"
						."</tr>"
						."<tr>"
							."<td><input type='text' placeholder='Phone *' name='phone' class='contactinput' value=\"".(isset($_POST['phone'])?htmlspecialchars(stripslashes($_POST['phone'])):"")."\" /></td>"
						."</tr>"
						."<tr>"
							."<td>"
								."<textarea name='message' placeholder='Message *' class='contacttextarea'>".(isset($_POST['message'])?htmlspecialchars(stripslashes($_POST['message'])):"")."</textarea>"
							."</td>"
						."</tr>"
						.(self::captcha?""
							."<tr>"
								."<td>"
									.reCAPTCHA::getChallengeHTML()
								."</td>"
							."</tr>"
							:""
						)
						."<tr>"
							."<td id='contactsubmitspace'>"
								."<input type='submit' name='submit' value=' Submit ' />"
							."</td>"
						."</tr>"
					."</tbody>"
				."</table>"
			."</form>"
			."<div id='requiredNote'><span class='contactFormRequired'>*</span> Required</div>"
		;
		return $out;
	}

	/**
	* Save the Post Contact Data
	* @return int - The Id of the newly created row or true if there is no AUTO_INCREMENT column
	*/
	private static function saveContact()
	{
		global $database_mysqli_local;
		global $SITE_DATABASE;
		$return=$database_mysqli_local->mysqlidb->insertInto($SITE_DATABASE,'contacts',array(
			'first_name'=>$_POST['first_name'],
			'last_name'=>$_POST['last_name'],
			'email'=>$_POST['email'],
			'phone'=>$_POST['phone'],
			'message'=>$_POST['message'],
		));
	}
}
?>