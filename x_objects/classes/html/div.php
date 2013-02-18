<?php 
/*
 * @Project:	Platform
 * @Module:		DivElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Sept 26, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Element
 */
class div extends HTMLElement {

	private $Debug = true;

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the parent element name
		$this->ElementName = 'div';
		
		parent::__construct($Attributes, $InnerHTML, $displayOnConstruct);	
	}

	//! create a new one
	public static function create( $attr, $html, $display = null) {
	
		$class = __CLASS__;
		
		return new $class( $attr, $html, $display );
	}
	
}
?>
