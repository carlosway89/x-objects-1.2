<?php 
/*
 * @Project:	Platform
 * @Module:		SpanElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Sept 26, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Span Element
 */
class span extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		$this->ElementName = 'span';
		
		parent::__construct( $Attributes, $InnerHTML, $displayOnConstruct );
			
	}

	
}
?>