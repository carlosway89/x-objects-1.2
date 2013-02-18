<?php 
/*! HTMLElement representation of an HTML table.
 * 
 * @Project:	Platform
 * @Module:		TDElement
 * @Extends:	HTMLElement (represents any HTML element)
 * @Created:	Sept 29, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Element
 */
class TableElement extends HTMLElement {

	//! the table header, which is an HTMLObject
	private $tableHeader = null;
	
	//! the table content, which is an array of objects
	private $tableContent = null;
	
	/*
	 * when constructing, set certain atrributes
	 */
	function __construct( $Attributes = null, $InnerHTML = null , $displayOnConstruct = false) {

		// set the element name
		$this->ElementName = 'table';
		
		if ( $Attributes )
			$this->setAttributes( $Attributes, ',');
					
		if ( $InnerHTML)
			$this->setInnerHTML( $InnerHTML );
			
		if ( $displayOnConstruct)
			$this->display();
			
	}
	
	
	//! adds a table header
	public function addHeader( $header ) {
	
		if ( ! is_object( $header) || ! get_class( $header ) === 'TableHeaderElement' )
			throw new IllegalArgumentException( 'TableElement::addHeader(): argument must be object of type TableHeaderElement');
		
		$this->tableHeader = $header;
		
	}
	
	//! adds content to the table
	public function addContent( $content ) {
	
		if ( ! is_array( $content ) )
			throw new IllegalArgumentException('TableElement::addContent(): argument must be an array of Objects');
					
		$contentArray = array();
		foreach ( $content as $element )
			array_push ( $contentArray , new TRElement('class=TRElement',$element));
		
		$this->tableContent = $contentArray;
		
	}
	
	
}
?>