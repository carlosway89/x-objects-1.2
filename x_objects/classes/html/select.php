<?php 
/*! create a new dynamic HTMLElement which is a SELECT element
 * @Project:	Platform
 * @Module:		SelectElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	14 Nov 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML SELECT Element
 */
class select extends HTMLInputElement {

	private $Debug = false;
	
	// the select options
	protected $Options = null;
	
	//! an optional HTML element to display right after for validation errors
	private $errorSpan = null;
	
	// they key for the selected value (if any)
	protected $selectedValue = '';

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( 
		$Attributes = null, 
		$InnerHTML = null,
		$Options = null , 
		$displayOnConstruct = false,
		$selectedValue = null) {

		
		// set options
		$this->Options = $Options;
		
		if ( $this->Debug)
			Debugger::echoMessage(get_class() . "default = $selectedValue<br>");
		
		// set selected value if present
		if ( $selectedValue )
			$this->selectedValue = $selectedValue;
		
		parent::__construct($Attributes, $InnerHTML, $Options, $displayOnConstruct);	
		
		// set the parent element name
		$this->ElementName = 'select';
		
	}
	
	//! synonym for below
	public function xhtml( $obj = null ) {
		return $this->getAsHTML( $obj );
	}
	
	/// override parent function for this different type of element
	public function getAsHTML( $obj = null ) { 
		
		$HTML = '<select ' . xQuery::parse( $this->getAttributesAsString(), $obj) . '>' . "\r\n";
		
		foreach ( $this->Options as $name => $value) {
		
			// first check the REQUEST
			$selected = null;
			// next check local value
			if ( !$selected && $this->selectedValue == $value ) {
				if ( $this->Debug )
					echo get_class() . " found a match for default $value<br>";
					
					$selected = ' selected="selected" ';
				}
			
			$HTML .= "\t" . '<option ' . $selected . ' value="' . $value . '">' . $name . '</option>'. "\r\n";
		
		}
		$HTML .= '</select>'. "\r\n";
		
		return $HTML;
		}
	
	//! get as a string
	public function getAsString() { return $this->getAsHTML(); }
	
	//! set the options for this element
	public function setOptions( $options ) { $this->Options = $options; }
	
	//! set or get the error span
	public function errorSpan ( $span = null ) {
	
		if ( $span )
			$this->errorSpan = $span;
		else return $this->errorSpan;
		
	}
	
	//! get options xhtml, with a default
	public function options_xhtml( $default = null ) {
	
	
		$HTML = '';	
					
		foreach ( $this->Options as $name => $value) {
		
					// first check the REQUEST
					$selected = null;
					
					if ( isset( $_REQUEST[$this->get('id')] ) && $_REQUEST[$this->get('id')] == $value )
						$selected = ' selected=selected ';				
					// next check local value
					if ( !$selected && $default == $value )
						$selected = ' selected=selected ';
			
					$HTML .= "\t" . '<option ' . $selected . ' value="' . $value . '">' . $name . '</option>'. "\r\n";
		}
		return $HTML;

	}
	
	//! magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			case 'options_xhtml':
			
				
				return $this->options_xhtml();
		
				
			break;
		
			default:
			
				return $this->get( $what );
				
			break;
		
		}
	}
}
?>
