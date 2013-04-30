<?php
/**
 * An X-Object is a web component "Controller" that links a Business Object ("model")
 * with an HTML/PHP "View" packaged nicely so it can be invoked anywhere on the page
 * or within an Ajax Controller.
 *
 * The main purpose of an X-Object is to facilitate re-use of code and better abstraction
 * for rendering repeating and complex components
 *
 * @author David Owen Greenberg <david.o.greenberg@gmail.com>
 */
class x_object {
    public $business_object = null; // grant access to displayed object
    private $debug = false;
    private $xml = null;
	private $attributes = null;
	private $key = null;
    public $webapp_location = null;

    /**
     * Create a new Business Object
     * @param $key string unique Id for X-Object View File
     * @param $attributes
     * @param null $xml
     */
    public function __construct( $key, $attributes = null, $xml=null  ) {
		global $container,$webapp_location, $xobjects_location;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $this->webapp_location = $webapp_location;
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
		$this->attributes = $attributes;
	}

    /**
     * return the X-Object as an xHTML string
     * @param $busObj object the Business Object to display
     * @param $log bool whether or not to log the action
     * @return string the xHTML representation
     */
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
            // synonyms to grab the business object
            case 'busobj':case 'bus_obj':case 'bobj':case 'obj':case 'the_object':
                return $this->business_object;
            break;
			case 'xhtml':
				$xhtml = $this->xhtml();
				return $xhtml;
				
			break;
            // New! You can grab the business object by it's classname directly
            default:
                $classname = get_class($this->business_object);
                return $what == $classname?$this->business_object:null;
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

    public function __toString(){
        try {
            $str = $this->html();
        } catch (Exception $e){
            $str = $e->getMessage();
        }
        return $str;
    }
	
}

?>