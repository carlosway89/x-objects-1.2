<?php
/**
 * This core class is a Singleton that represents the X-Objects instance, or application container
 */
class api_manager extends magic_object {

	//! the instance
	private static $instance = null;
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	}
	
	public function __get ( $what ) {
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		switch ( $what ) {
			// twitter api
			case 'twitter':
			case 'twitter_api':
				return new twitter_api();
			break;
			case 'facebook':
			case 'facebook_api':
				global $facebook_config;
				return new facebook($facebook_config);
			break;
			default:
				if ( ! isset( $this->$what )) {
					$msg="<span style=\"color: red;\">$tag->event_format : The application code attempted to access an undefined property <span style=\"font-weight:bold;color:green\">'$what'</span></span>";
					$container->log( xevent::warning, $msg );
					trigger_error( $msg, E_USER_WARNING);
					return false;
				} else
					return $this->$what;
			break;
		}
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