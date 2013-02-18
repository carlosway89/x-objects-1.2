<?php 
/*! object representation of an <TH> element.
 * 
 * @Project:	Platform
 * @Module:		THElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	january 26, 2011
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML TH Element
 */
class TableHeaderElement extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the element name
		$this->ElementName = 'tr';
		
		if ( $Attributes )
			$this->setAttributes( $Attributes, ',');

		if ( ! is_array( $InnerHTML ) )
			throw new IllegalArgumentException( 'TableHeaderElement::__construct(): 2nd argument must be an array of HTMLElements');
			
		if ( $InnerHTML)
			$this->setInnerHTML( $InnerHTML );
			
		if ( $displayOnConstruct)
			$this->display();
			
	}
	
}
?>