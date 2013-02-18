<?php 
/*! dynamically create/update an html BUTTON.
 * @Project:	Platform
 * @Module:		ButtonElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Oct 1, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Button
 */
class button extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( 
	
		$Attributes = null,		// e.g. 'id=mybutton,type=submit' 
		$InnerHTML = null , 	// value of the button as text
		$displayOnConstruct = false, // should it be displayed when constructed?
		$AttribSeparator = ',') {	 // character to separate attributes

		$this->ElementName = 'button';
		
		// construct parent
		parent::__construct( $Attributes, $InnerHTML, $displayOnConstruct);
		
	}
	
}
?>
