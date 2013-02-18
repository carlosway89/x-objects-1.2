<?php 
/*
 * @Project:	Platform
 * @Module:		TDElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Sept 29, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Element
 */
class TDElement extends HTMLElement {

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		$this->ElementName = 'td';
		
		parent::__construct( $Attributes, $InnerHTML, $displayOnConstruct);
			
	}
		
}
?>