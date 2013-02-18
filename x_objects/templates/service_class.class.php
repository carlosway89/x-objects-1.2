<?php
/**
 * template for creating a new service class
 */
class service_class extends magic_object implements x_service {

	//! the instance
	private static $instance = null;
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	
	}
	
	public function __destruct(){
	}
	
	//! returns a reference to the singleton instance of the class
    public static function instance() 
    {
		// if the instance hasn't been created yet
        if (!isset(self::$instance)) {
			// use the current classname
            $C = __CLASS__;
			// and create the instance as a new object of that class
            self::$instance = new $C;
        }

		// return a reference to the instance
        return self::$instance;
    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	

}

?>