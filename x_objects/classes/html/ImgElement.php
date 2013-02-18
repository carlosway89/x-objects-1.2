<?php 
/*
 * @Project:	Platform
 * @Module:		classes/ImgElement
 * @Extends:	classes/HTMLElement
 * @Created:	20 Oct 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Img Element
 */
 class ImgElement extends HTMLElement {

	// private debugging
	private $Debug = false;
	
	function __construct( $Attributes = null , $Src = null, $displayOnLoad = false) {

		$this->ElementName = 'img';
		
		if ( $Attributes )
			$this->setAttributes( $Attributes, ',');
		
		if ( $Src )
			$this->set('src',$Src);
			
		$this->setInnerHTML( '');
		
		if ( $displayOnLoad )
			$this->display();
		
	}
	
	 
	
	
}
?>
