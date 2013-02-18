<?php

//! country picker
class x_age_picker extends AttributedObject {

	//! toggle debugging
	private $debug = false;

	//! construct with xml
	public function __construct( $min, $max , $id, $default, $obj = null ) {

		$this->min = $min;
		$this->max = $max;
		$this->obj = $obj;
		$this->id = $id;
		$this->default = $default;
		
		// try to get it from the xCache if possible, otherwise create it and add it to the xCache
		
		// key for xCache
		$key = 'x-age-picker';

		// create an array of ages
		$ages = array();
		
		for ( $age = $min; $age <= $max ; $age++)
			$ages[ $age ] = $age;
			
		$this->ages = $ages;
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
		$html .= '<option value="">__</option>';
		
		foreach ( $this->ages as $age ) {
		
			$selected = $this->default == $age ? 'selected="selected"' : '';
			
			$html .= '<option ' . $selected . ' value="' . $age . '">' . $age . '</option>';
			
		}
		
		$html .= '</select>';
	
		return $html;
	}

	//! create a new one
	public static function create( $min, $max, $id, $default, $obj = null ) {
		return new x_age_picker( $min, $max, $id, $default, $obj);
	}
	
}

?>