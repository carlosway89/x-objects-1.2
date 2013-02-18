<?php
/**
 * This core class is a Singleton that represents the X-Objects instance, or application container
 */
class xml_cache  {

	//! the instance
	private static $instance = null;
	
	//! the cache
	private $cache = array();
	
	//! private constructor -- singleton
	private function __construct() { 
		// set up logging and debugging
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
	}
	
	public function __get ( $what ) {
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		if ( isset( $this->cache[$what]))
			return $this->cache[$what];
		else { 
			// attempt to load it as a global
			global $$what;
			if ( ! isset( $$what ))
				$container->warn("<span style=\"color: red;\">$tag->event_format : Unable to locate the xml string named <span style=\"font-weight:bold;color:green\">'$what' </span> Please make sure it is defined in your controller, or in global scope.</span>" );
			elseif ( ! is_string( $$what ))
				$container->warn("<span style=\"color: red;\">$tag->event_format : The requested object is not a string: <span style=\"font-weight:bold;color:green\">'$what' </span> Please make sure it is defined as a string in your controller, or in global scope.</span>");
			else { 
				$src = $$what;
				$xml = @simplexml_load_string( $src);
				if ( ! is_object( $xml))
					$container->warn("<span style=\"color: red;\">$tag->event_format : The requested object is not an xml string: <span style=\"font-weight:bold;color:green\">'$what' </span> Please make sure it is defined as a string in your controller, or in global scope.</span>");
				else { 	
					$this->$what = $xml;
					return $this->cache[ $what ];
				}
			}
		}
	}
	
	//! magic set
	public function __set($what, $how){
		$this->cache[$what] = $how;
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