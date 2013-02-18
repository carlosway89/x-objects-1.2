<?php

//! country picker
class x_price_picker extends AttributedObject {

	//! toggle debugging
	private $debug = false;

	//! construct with xml
	public function __construct( $id, $default, $low, $high, $increment, $currency, $obj = null ) {

		$this->id = $id;
		$this->obj = $obj;
		$this->default = $default;
		$this->low = $low;
		$this->high = $high;
		$this->increment = $increment;
		$this->currency = $currency;
				
		// create an array of heights
		$amounts = array();
		
		for ( $amount = $low; $amount <= $high ; $amount += $increment) {	
			$amounts[ $amount ] = "$" . "$amount";
		}
		$this->amounts = $amounts;
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
		
		foreach ( $this->amounts as $key => $amount ) {
		
			$selected = $this->default == $key ? 'selected="selected"' : '';
			
			$html .= '<option ' . $selected . ' value="' . $key . '">' . "$" . number_format( $key) . '</option>';
			
		}
		
		$html .= '</select>';
	
		return $html;
	}

	//! create a new one
	public static function create( $id, $default, $low, $high, $increment, $currency, $obj = null ) {
		return new x_price_picker( $id, $default, $low, $high, $increment, $currency, $obj);
	}
	
}

?>