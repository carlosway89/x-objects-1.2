<?php
/**
 * this SINGLETON service class is the main workhorse for the application
 * @author David Owen Greenberg <david@x-objects.org>
 */
class _appname_ implements x_service {
    private static $instance = null;
 	private static $debug = false;
    private $cache = null;
	private function __construct(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->app_debug){
            echo $tag->event_format . ": app service singleton initialized<br>\r\n";
        }
	}
	//! returns a reference to the singleton instance of the class
    public static function instance() {
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

    public function destroy(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug || $container->app_debug){
            if ( $this->performance)
                echo self::twb . $tag->event_format . ": performance = $this->performance".self::twe ."<br>\r\n";
            echo $tag->event_format . ": app service destroyed<br>\r\n";
        }
        self::$instance = null;
    }
	// Prevent users to clone the instance
    public function __clone(){ trigger_error('Clone is not allowed.', E_USER_ERROR);}
    
    public function __call($f,$a){
    	$this->last_error = get_class()."::$f is not a recognized function (error 1)";
    	return false;	
    }
    
    public function __get( $what ){
    	global $container;
    	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
    	$s = new SESSION;
    	// collect result and send at end, better for debugging
    	$result = null;
    	switch( $what){
    	}
        //if ( $container->app_debug) echo "$tag->event_format : found $what, result = $result<br>\r\n";
    	return $result;
    }

    
}
?>
