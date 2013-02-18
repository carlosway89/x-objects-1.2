<?php
/**
 * display an html select element using dynamic database values
 */
class x_select extends AttributedObject {

    /**
     * @param $key
     * @param $values
     * @param $names
     * @param null $query optional X-Objects query to narrow selection
     * @param null $obj
     * @param null $id
     * @param null $class
     * @param null $default
     * @param null $title
     * @param null $multiple
     */
    public function __construct( $key, $values, $names, $query = null , $obj = null, $id = null, $class = null, $default = null, $title =null,$multiple=null ) {
        global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        $this->obj = $obj;
		$this->id = $id;
		$this->class = $class;
		$this->default = $default;
		$this->title = $title;
		$this->multiple = (int)$multiple;
		
		if ( $this->debug )
			echo get_class() . "default $default $this->default<br>";
		
		// try to get it from the xCache if possible, otherwise create it and add it to the xCache
		
		// key for xCache
		$xkey = $key . '-' . (string) $values . (string) $names;
		
		// try to get from xCache, and if not present, create and add it to xCache
		// note that set returns the object that was set, for chaining of commands
		$this->nv_pairs = xCache::exists( $xkey ) ?
			xCache::get( $xkey ) :
			xCache::set( $xkey, 
				ids_names_array::create( $key, $values, $names, $query
				)->the_array );

        if ( $container->debug) echo "$tag->event_format: nv pairs is ". new xo_array($this->nv_pairs). "<br>\r\n";
		
	}
	
	public function __set( $what, $val ) { $this->set( $what, $val ); }
	
	public function __get( $what ) {
	
		switch ( $what ) {
		
			default:
				return $this->get( $what );
			break;
		}
	}
	
	// return as well formed html
	public function xhtml() {
	    global $container;
        $tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $hid = $this->id;
		$class = $this->class;
		
		$multiple = ( $this->multiple && is_numeric($this->multiple))? " multiple size=\"$this->multiple\" ": "";
		
		$html = '<select '.$multiple . ' title="'.$this->title . '" style="' . $this->xml['style'] . '" class="'. $class . '" name="' . $hid . '" id="'.  $hid .'">';
		
		// add default option
        $c = $container->userstring("choose");
		$html .= '<option value="">'.$c.'</option>';
		
		foreach ( $this->nv_pairs as $id => $name ) {
            if ( $container->debug) echo "$tag->event_format: setting pair for $id => $name<br>\r\n";
		    $selected = $this->default == $id ? 'selected="selected"' : '';
			// set optional class for multiple values
            // set optional class for multiple values
            if ( preg_match('/\,/',$id)) {
                $vals = explode(',',$id);
                $class = ' class="alt-'.$vals[1].'" ';
            }

            $html .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';



        }
        $html .= '</select>';

        return $html;
	}

	//! create a new one
	public static function create( $key, $values, $names, $query = null, $obj = null, $id = null, $class = null, $default = null ,$title=null,$multiple=null) {
		return new x_select( $key, $values, $names, $query, $obj, $id, $class, $default,$title,$multiple);
	}

    public function __toString(){
        $str = "";
        try {
            $str = $this->xhtml();
        } catch ( Exception $e){
            $str = $e->getMessage();
        }
        return $str;
    }
	
}

?>