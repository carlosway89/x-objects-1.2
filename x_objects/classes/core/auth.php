<?php

class auth extends magic_object {
	const json_error = '{ "error" : "1" , "error_text" : "login failed" }';

	//! execute an authentication check
	public static function execute( $key, $username, $password ) {
		// try retrieving the user a few different ways
		$class = new $key("username='$username'");
		if ( ! $class->exists )
			$class = new $key("email='$username'");
		return $class->authenticate( $password );
	
	}
	
	//! log user out
	public static function logout() {
	
		global $container;
		$me = $container->me;
		$container->logout();
		return "success auth logout $me->username";
		
	}

}


?>