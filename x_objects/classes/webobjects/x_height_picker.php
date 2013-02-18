<?php

//! country picker
class x_height_picker extends AttributedObject {

	//! toggle debugging
	private $debug = false;

	//! construct with xml
	public function __construct( $id, $default, $obj = null ) {

		$this->id = $id;
		$this->obj = $obj;
		$this->default = $default;
				
		// create an array of heights
		$heights = array();
		
		for ( $height = 4.00; $height <= 7.00 ; $height = $height + 0.01) {	
			if ( $height - floor( $height)  > 0.11)
				$height =  ceil( $height );
			$heights[ "$height" ] =  
				floor( $height) . "' " . 
					round(($height - floor($height))*100) . '"';
				
		}
		$this->heights = $heights;
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
		
		foreach ( $this->heights as $key => $height ) {
		
			$selected = $this->default == $key ? 'selected="selected"' : '';
			
			$html .= '<option ' . $selected . ' value="' . $key . '">' . $height . '</option>';
			
		}
		
		$html .= '</select>';
	
		return $html;
	}

	//! create a new one
	public static function create( $id, $default, $obj = null ) {
		return new x_height_picker( $id, $default, $obj);
	}
	
}

?>