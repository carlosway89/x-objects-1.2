<?php

class factory extends magic_object implements x_service {
	//! singleton intance
	private static $instance = null;
	//! private constructor
	private function __construct(){}
	//! get the instance
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
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

	// create an object from the API
	public static function create( $key, $args , $view) {
	
		//echo "$key $args";
		
		// create new object
		$obj = new $key();
		
		// check for xml
		if ( preg_match( '/<\/div>/', $args ) ) {
		
			$xml = simplexml_load_string( stripslashes( $args ) );
			
			// go through and set
			foreach ( $xml->children() as $node ) {
				$member = (string) $node['id'];
				
				
				$obj->$member = (string) $node;
				
			}
		} else {
		
			// get args
			$vals = new args( $args );
		
			// set them
			foreach ( $vals->members as $name => $value ) {
			
				
				//echo "$name = $value<br>";
				$obj->$name = $value;
			}
		}
		
		// save object
		$obj->save();
		
		// return result
		if ( $obj->exists )
			echo '<div class="factory-wrapper">' . $obj->xhtml( $view ) . '</div>';
		else
			echo "error factory create $key $args not saved";
	
	}
	
	//! create an array of objects
	public static function create_array( $key, $args , $view) {
	
	}
	
	//! get an array of numeric ids for the objects
	public static function ids( $objects ) {
	
		$ids = array();
		foreach ( $objects as $obj ) $ids[ count($ids)] = $obj->id;
		return $ids;
	}
	
	//! fetch objects from the data store
	public static function fetch( $key, $query, $view ){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( $container->debug)
			echo "$tag->event_format : key = $key, query=$query<br>\r\n";
		$fetch = ObjectFactory::create( $key, null, null, null, null, $query);
		$html = "";
		foreach ( $fetch as $obj)
			$html .= $obj->xhtml($view);
		if ($container->debug )
			echo "$tag->event_format : html = $html<br>\r\n";
		echo $html;
	}

}

?>