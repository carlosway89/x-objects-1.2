<?php

//! country picker
class x_country_picker extends AttributedObject {

	//! toggle debugging
	private $debug = false;

	//! construct with xml
	public function __construct( $id, $class, $default , $obj = null ) {
	
		$this->id = $id;
		$this->class = $class;
		$this->default = $default;
		$this->obj = $obj;
		
		// try to get it from the xCache if possible, otherwise create it and add it to the xCache
		
		// key for xCache
		$key = 'x-country-picker';
		
		// try to get from xCache, and if not present, create and add it to xCache
		// note that set returns the object that was set, for chaining of commands
		
		
		$this->nv_pairs = xCache::exists( $key ) ?
			xCache::get( $key ) :
			xCache::set( $key, 
				ids_names_array::create( "country" , 'iso', 'display_name' )->the_array );
		
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
			
		$html = '<select style="' . $this->xml['style'] . '" class="'. $this->class . '" id="'.  $this->id .'" name="' . $this->id . '">';
		
		// add dummy row
		$html .= '<option value="">__Please Choose__</option>';
		
		foreach ( $this->nv_pairs as $id => $name ) {
		
			$selected = $this->default == $id ? 'selected="selected"' : '';
			
			$html .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
			
		}
		
		$html .= '</select>';
	
		return $html;
	}

	//! create a new one
	public static function create( $id, $class, $default, $obj = null ) {
		return new x_country_picker( $id, $class, $default, $obj);
	}
	
}

?>