<?php
/**
* Class to make sending email easier...
*/
class sendEmail
{
	/**
	* Send SMTP Mailgun Email
	* @param string $from - the email from address
	* @param string $fromName - the name of the from person
	* @param string $to - the email to address
	* @return bool - true if sent
	**/
	public static function sendSMTPMailgun($from,$fromName,$to,$subject,$body)
	{
		require_once __DIR__.'/PHPMailer/PHPMailerAutoload.php';

		$mail = new PHPMailer;
		global $MAILGUN_SMTP_USERNAME;
		global $MAILGUN_SMTP_PASSWORD;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $MAILGUN_SMTP_USERNAME; //SMTP username
		$mail->Password = $MAILGUN_SMTP_PASSWORD; // SMTP password
		$mail->SMTPSecure = 'tls'; // Enable encryption, only 'tls' is accepted
		$mail->Port = '2525';

		$mail->From = $from;
		$mail->FromName = $fromName;
		$mail->addAddress($to);                 // Add a recipient

		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters

		$mail->Subject = $subject;
		$mail->Body = $body;

		if(!$mail->send())
			return false;
		return true;
	}

	/**
	* Send a text email
	* @param string $to - The email to send to
	* @param string $subject - The subject of the email
	* @param string $message - The message of the email
	* @return bool - Response from the php mail function - TRUE if successfully sent
	*/
	public static function text($to=NULL,$subject="",$message="",$from=NULL)
	{
		if(!$to)
			throw new Exception("No To");
		if(!$from)
			$from="noreply@".$_SERVER['SERVER_NAME'];
		if(!isset($replyTo)||!$replyTo)
			$replyTo=$from;
		$headers=""
			."MIME-Version: 1.0\n"
			."From: ".$from."\n"
			."Reply-To: ".$replyTo."\n"
			."Content-Type: text/plain; charset=\"UTF-8\"\n"
		;

		return self::sendSMTPMailgun($from,'',$to,$subject,$message);
	}

	/**
	* Send a HTML email
	* @param string $to - The email to send to
	* @param string $subject - The subject of the email
	* @param string $message - The message of the email
	* @return bool - Response from the php mail function - TRUE if successfully sent
	*/
	public static function html($to=NULL,$subject="",$message="",$from=NULL)
	{
		if(!$to)
			throw new Exception("No To");
		$sender="noreply@".$_SERVER['SERVER_NAME'];
		if(!$from)
			$from=$sender;
		if(!$replyTo)
			$replyTo=$from;

		$headers=""
			."MIME-Version: 1.0\n"
			."From: ".$from."\n"
			."Reply-To: ".$replyTo."\n"
			."Sender: ".$sender."\n"
			."Content-Type: text/html; charset=\"UTF-8\"\n"
		;

		return self::sendSMTPMailgun($from,'',$to,$subject,$message);
	}

	/**
	* Verify this is an email address...
	* @param string $email - The email to be verified
	* @return bool - True if this is a good email address & false if it is not
	*/
	public static function verifyEmail($email)
	{
		if(preg_match('/^[^0-9][a-zA-Z0-9_.-]+([.][a-zA-Z0-9_.-]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/',$email))
			return true;
		return false;
	}

}
?>