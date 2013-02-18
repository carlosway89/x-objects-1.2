<?php 
/*
 * @Project:	Platform
 * @Module:		classes/FormElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Oct 19, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Button
 */
class FormElement extends HTMLElement {
	
	// array of fields
	private $Fields = array();

	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( 
	
		$Attributes = null,		// e.g. 'id=mybutton,type=submit' 
		$InnerHTML = null , 	// value of the button as text
		$displayOnConstruct = false, // should it be displayed when constructed?
		$AttribSeparator = ',') {	 // character to separate attributes

		$this->ElementName = 'form';
		
		if ( $Attributes ) 
			$this->setAttributes( $Attributes , $AttribSeparator);
			
		if ( $InnerHTML)
			$this->setInnerHTML( $InnerHTML );
			
		if ( $displayOnConstruct)
			$this->display();
			
	}
	
	//! add a new field (HTMLElement) to the form
	public function addField( $Attr) {
		
		$this->Fields[count($this->Fields)] = new InputElement( $Attr);
		
		// set the inner html as the new set of fields
		$this->setInnerHTML( $this->Fields );
		
	}
	
}
?>
