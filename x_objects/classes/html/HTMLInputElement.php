<?php 
/*
 * @Project:	Platform
 * @Module:		HTMLInputElement
 * @Extends:	HTMLElement (represents HTML elements in general)
 * @Created:	Sept 26, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Input Element
 */
abstract class HTMLInputElement extends HTMLElement {

	protected $Options = null;
	
	public function __construct( $Attributes, $HTML, $Options, $displayOnConstruct) {
		
		parent::__construct( $Attributes, $HTML, $displayOnConstruct);
		
		$this->Options = $Options;
		
	}
	
}
?>