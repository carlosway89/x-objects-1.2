<?php 
//! dynamic HTML element representation of "<br>"

class BRElement extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the parent element name
		$this->ElementName = 'br';
		
		parent::__construct($Attributes, $InnerHTML, $displayOnConstruct);	
	}
	
}
?>
