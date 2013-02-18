<?php 
/*! Dynamic object to instantiate an <a> tag in HTML.
 * @Project:	Platform
 * @Module:		classes/UnorderedListElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Oct 27, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML UL Element
 */
class AnchorElement extends HTMLElement {

	private $Debug = false;

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( 
		$Attributes = null,				// pass html tag attributes when constructing 
		$InnerHTML = null , 			// pass the inner html, which can also be HTML objects
		$displayOnConstruct = false		// display the element when constructing?
	) {

		// set the parent element name
		$this->ElementName = 'a';
		
		parent::__construct( $Attributes, $InnerHTML, $displayOnConstruct);
			
	}
	
	
}
?>
