<?php
require_once dirname(__FILE__)."/reCAPTCHA.php";
require_once dirname(__FILE__)."/loginModel.php";
/**
Login View Class
*/
class loginView
{
	const LWidth=110; //width for left column of forms

	public static function getLoginForm()
	{
		zInterface::addBotJS(""
			."$('#loginForm').submit(function(e){"
				."e.preventDefault();"
				."e.stopPropagation();"
				."$.post('/h/account/accountLogin.php',$(this).serialize(),function(data){"
					."eval(data);"
				."});"
			."});"
			."$('#forgotPasswordLink').click(function(e){"
				."$.post('/h/account/accountForgotPassword.php',$('#loginForm').serialize(),function(data){"
					."eval(data);"
				."});"
			."});"
		);

		return ""
			."<table border='0' cellpadding='5' cellspacing='0' width='100%'>"
				."<form action='' method='post' id='loginForm'>"
					."<tr>"
						."<td colspan='2'>"
							."<b>"
								."Login to your account"
							."</b>"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Email"
						."</td>"
						."<td>"
							."<input type='text' name='email' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Password"
						."</td>"
						."<td>"
							."<input type='password' name='password' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td colspan='2' align='right'>"
							."<a href='javascript:void(0);' id='forgotPasswordLink'>"
								."Forgot Password?"
							."</a>"
							." &nbsp; "
							."<input type='submit' name='login' value=' login ' align='absmiddle' />"
						."</td>"
					."</tr>"
						."<input type='hidden' name='url' value=\"".(isset($_REQUEST['url'])?urlencode(urldecode($_REQUEST['url'])):"")."\" />"
				."</form>"
			."</table>"
		;
	}

	public static function getSignupForm()
	{
		global $SITE_TOS_URL;
		global $SITE_PRIVACY_URL;
		global $RECAPTCHA_SITE_KEY;
		global $RECAPTCHA_SECRET_KEY;

		zInterface::addBotJS(""
			."$('#signupForm').submit(function(e){"
				."e.preventDefault();"
				."e.stopPropagation();"
				.(isset($RECAPTCHA_SITE_KEY)&&$RECAPTCHA_SITE_KEY&&isset($RECAPTCHA_SECRET_KEY)&&$RECAPTCHA_SECRET_KEY?""
					."$('#zconfirmpostrecap').remove();$(this).append(\"<input id='zconfirmpostrecap' type='hidden' name='recap' value='\"+$('#g-recaptcha-response').val()+\"'/>\");":"")
				."$.post('/h/account/accountCreate.php',$(this).serialize(),function(data){"
					."eval(data);"
				."});"
			."});"
		);

		return ""
			."<table border='0' cellpadding='5' cellspacing='0' width='100%'>"
				."<form action='' method='post' id='signupForm'>"
					."<tr>"
						."<td colspan='2'>"
							."<b>"
								."Need an account? Signing up is easy."
							."</b>"
							."<br/>"
							."<b style='color:#800000;'>"
								."Note:"
							."</b>"
							." all fields are required."
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."First Name"
						."</td>"
						."<td>"
							."<input type='text' name='first_name' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Last Name"
						."</td>"
						."<td>"
							."<input type='text' name='last_name' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Email"
						."</td>"
						."<td>"
							."<input type='text' name='email' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Confirm Email"
						."</td>"
						."<td>"
							."<input type='text' name='emailConfirm' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Password"
						."</td>"
						."<td>"
							."<input type='password' name='password' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Confirm Password"
						."</td>"
						."<td>"
							."<input type='password' name='passwordConfirm' style='width:100%;' />"
						."</td>"
					."</tr>"
					.(isset($SITE_TOS_URL)&&$SITE_TOS_URL?""
						."<tr><td colspan='2'>"
						."By using our site and creating an account, you agree that you have read and accepted our <a href='".$SITE_TOS_URL."'>Terms of Service</a>, "
						."you are at least 18 years old, ".(isset($SITE_PRIVACY_URL)?"and you consent to our <a href='".$SITE_PRIVACY_URL."'>Privacy Notice</a> ":"")."and receiving email communications from us."
						."</td></tr>"
					:"")
					.(isset($RECAPTCHA_SITE_KEY)&&isset($RECAPTCHA_SECRET_KEY)&&$RECAPTCHA_SECRET_KEY&&$RECAPTCHA_SITE_KEY?""
						."<tr><td colspan='2'>"
							.reCAPTCHA::getChallengeHTML($RECAPTCHA_SITE_KEY)
						."</td></tr>"
					:"")
					."<tr>"
						."<td colspan='2' align='right'>"
							."<input type='submit' name='createAccount' value=' create account ' align='absmiddle' />"
						."</td>"
					."</tr>"
						."<input type='hidden' name='url' value=\"".(isset($_REQUEST['url'])?urlencode(urldecode($_REQUEST['url'])):"")."\" />"
				."</form>"
			."</table>"
		;
	}

	public static function getResetPasswordForm($userid,$hash)
	{
		zInterface::addBotJS(""
			."$('#resetPasswordForm').submit(function(e){"
				."e.preventDefault();"
				."e.stopPropagation();"
				."$.post('/h/account/accountResetPassword.php',$(this).serialize(),function(data){"
					."eval(data);"
				."});"
			."});"
		);
		return ""
			."<table border='0' cellpadding='5' cellspacing='0' width='100%'>"
				."<form action='' method='post' id='resetPasswordForm'>"
					."<tr>"
						."<td colspan='2'>"
							."<b>"
								."Password Reset:"
							."</b>"
							."<br/>"
							."<b style='color:#800000;'>"
								."Note:"
							."</b>"
							." all fields are required."
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."New Password"
						."</td>"
						."<td>"
							."<input type='password' name='password' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td width=".self::LWidth.">"
							."Confirm New Password"
						."</td>"
						."<td>"
							."<input type='password' name='passwordConfirm' style='width:100%;' />"
						."</td>"
					."</tr>"
					."<tr>"
						."<td colspan='2' align='right'>"
							."<input type='submit' name='login' value=' update ' width='55' height='24' align='absmiddle' />"
						."</td>"
					."</tr>"
						."<input type='hidden' name='userid' value='".$userid."' />"
						."<input type='hidden' name='hash' value='".$hash."' />"
						."<input type='hidden' name='url' value=\"".(isset($_REQUEST['url'])?urlencode(urldecode($_REQUEST['url'])):"")."\" />"
				."</form>"
			."</table>"
		;
	}

	public static function getLoginBoxStyle()
	{
		return "margin:0 auto;padding:10px 10px 5px 5px;width:350px;background-color:#e9e7e9;border:1px solid #d1d1d1;";
	}
}


?>