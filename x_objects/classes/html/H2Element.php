<?php 
//! dynamic HTML H2 element

class H2Element extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the parent element name
		$this->ElementName = 'H2';
		
		parent::__construct($Attributes, $InnerHTML, $displayOnConstruct);	
	}
	
}
?>
