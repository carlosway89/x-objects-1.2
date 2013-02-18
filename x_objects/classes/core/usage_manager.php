<?php
/**
 * This core class is a Singleton that represents the X-Objects instance, or application container
 */
class usage_manager {
	private $debug = false;

	//! the instance
	private static $instance = null;
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	}
	
	public function __get ( $what ) {
			global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		switch ( $what ) {
			case 'avg_pg_load_time':
				return $container->services->utilities->ms_display_of( db_metrics::metric('avg', 'xo_statistic',"type='page_load'","value"));
			break;
			case 'num_events':
				return db_metrics::metric( 'total', "xo_event");
			break;
			case 'debug':
				return $container->debug ? "enabled" : "disabled";
			break;
			case 'log_enabled':
				return $container->log_enabled?"enabled" : "disabled";
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