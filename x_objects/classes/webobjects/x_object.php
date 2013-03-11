<?php

/**
 * Description: An x_object() is one of the most important and useful classes of objects in the framework.  
 * It's a representation of a single entity, or a member of an array or grouping, that is bound to an 
 * XML "view" file.  As such, x_objects are used mainly to display objects within web pages, using
 * xQuery.
 * @author David Owen Greenberg <david.o.greenberg@gmail.com>
 */

// global needed for view
$business_object = null;
$resources = null;

class x_object {

    /**
     * Public member grants direct access to the object in the view file
     */
    public $business_object = null;

    private $debug = false;

	// the xml
	private $xml = null;
	
	// attributes
	private $attributes = null;
	
	private $key = null;
	
	//! construct with an optional file
	public function __construct( $key, $attributes = null, $xml=null  ) {
		global $container,$webapp_location, $xobjects_location;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        // the key
		$this->key = $key;
        // has the view been found?
        $found = false;
        /**
         * first try to get the PHP view file, so we can later deprecate the use of XML
         * files.  For now, we'll still try to get the XML, but only afterwards
         */
        $vfs = array(
            $webapp_location . "/app/views/$key.php",
            $webapp_location . "/app/views/pages/$key.php",
            $xobjects_location . "views/$key.php",

        );
        foreach( $vfs as $v){
            if (file_exists($v)){
                $this->view_file = $v;
                $found = true;
                break;
            }
        }

        if ( ! $found) try {
			// first if key is null,
			if (!$key) {
				// set xml
				$this->xml = new RealXML( is_object($xml)? $xml:simplexml_load_string($xml) );
			} else {
				$this->xml = new RealXML( $key );
			}
		} catch ( ObjectNotInitializedException $oe ) {
		
			throw new ObjectNotInitializedException("I can't display the X-Object <span style='font-weight:bold;color:green;'>$key</span>.  Are you sure <span style='color:blue;font-weight:bold;'>$key.php</span> or <span style='color:blue;font-weight:bold;'>$key.xml</span> exists in /app/views?");
		
		}
	
		$this->attributes = $attributes;
		if ( $container->debug)
			echo "$tag->event_format : DONE creating a new x-object of type $key<br>\r\n";
	}
	
	
	//! return as html
	public function html( $busObj = null,$log = false ) {
        $this->business_object = $busObj;
		global $container,$business_object,$page_vars,$resources;
      	$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug ) echo "$tag->event_format : generating html for this object, the business object is is ". get_class( $busObj) . "<br>\r\n";
        if ( $this->view_file){
            $business_object = $busObj;
            $view_vars = $page_vars;
            $html = '';
            require($this->view_file);
        } else
            $html = $this->xml->html( $busObj , $this->attributes );

		if ( $container->debug ) echo "$tag->event_format : DONE generating html for this object, the business object is is ". get_class( $busObj) . "<br>\r\n";
		
		return $html;
	}


	// synonym for above method
	public function xhtml( $busObj = null, $log = false ) {
        return $this->html($busObj,$log);
	}
	
	/**
     * manufacture a real web object (great for chaining commands)
     * @param string $key the unique key name identifier, used to lookup the XML file.
	 * @param array $attributes optional attributes passed to the x-object from parent invoker
     * @return object returns a new object of type x_object
     */ 
	public static function create( $key , $attributes = null ) { 
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		if ( $container->debug)
			echo "$tag->event_format: creating a new x-object with key $key<br>\r\n";
		return new x_object( $key, $attributes ); 
	}
	
	/**
     *(synonym for create() method.) manufacture a real web object (great for chaining commands)
     * @param string $key the unique key name identifier, used to lookup the XML file.
	 * @param array $attributes optional attributes passed to the x-object from parent invoker
     * @return object returns a new object of type x_object
     */ 
	 public static function get( $key_or_obj ) { 
		return (is_object($key_or_obj))? new x_object(null,null,$key_or_obj):x_object::create( $key_or_obj ); 
	}
	
	//! gets the html attributes, formatted for use
	private function get_html_attributes( $xml ) {
	
		$result = '';
		
		foreach ( $xml->attributes() as $name => $value )
			$result .= $name . '=' . $value . ',';
			
		return $result;
	}
	
	//! magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			case 'xhtml':
				$xhtml = $this->xhtml();
				return $xhtml;
				
			break;
		
		}
	}
	
	/**
     * render an x_object directly from its xml string
     * @param string $xml the xml string representing the x-object.
	 * @return object returns a new object of type x_object
     */ 
	public static function render( $xml ) {
		$obj = new x_object(null,null,$xml);
		return $obj;
	}
	
}

?>