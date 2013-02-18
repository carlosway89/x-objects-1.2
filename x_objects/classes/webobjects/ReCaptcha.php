<?php

global $directory_name;

require_once( PATHROOT . "$directory_name/3rdparty/recaptchalib.php");

//! php class representation of recaptcha web object
class ReCaptcha {

	// public key
	//const PUBLIC_KEY = '6LdsmsQSAAAAAH8nqy-TwzNiTzbesKMbzQ5DUqLR';
	const PUBLIC_KEY = '6LdP8sQSAAAAAL756j2BxBURpKgwgL8e4dbh3-U0';
	
	// private key
	//const PRIVATE_KEY = '6LdsmsQSAAAAAGiuZAKvmOq_NdCtpS2OXLp5RgaA'; 
	const PRIVATE_KEY = '6LdP8sQSAAAAADylDnj-e0iEFHdc5HtYmilr0alM'; 
	
	// return as well-formed xhtml
	public function xhtml() {
			
    	return recaptcha_get_html( self::PUBLIC_KEY );

	}

	//! is it valid?
	public function valid() {
	
		$resp = recaptcha_check_answer (
			self::PRIVATE_KEY,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) 
			return false;
		else return true;
	
	}
	
	// create one
	public static function create() {
	
		return new ReCaptcha();
		
	}

}

?>