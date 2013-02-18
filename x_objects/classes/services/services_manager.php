<?php

//! the ServiceManager class is used to obtain access to various service instances

class services_manager extends magic_object {

	//! magic get
	public function __get( $what){
		$class = new ReflectionClass($what);
		if ( ! $class->implementsInterface('x_service'))
			throw new WrongArgTypeException( 
				xo_basename(__FILE__) . " [ " . __LINE__ . " ] " . get_class() . "{} " . __FUNCTION__ . "(): $what must implement the x_service interface in order " .
				"to act as an x-objects service.");
		return call_user_func("$what::instance");
	}

}

?>