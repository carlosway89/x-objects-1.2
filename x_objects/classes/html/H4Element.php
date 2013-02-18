<?php 
//! dynamic HTML H2 element

class H4Element extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the parent element name
		$this->ElementName = 'H4';
		
		parent::__construct($Attributes, $InnerHTML, $displayOnConstruct);	
	}
	
}
?>
