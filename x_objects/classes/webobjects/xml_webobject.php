<?php

//! an xml_webobjects is used to display a SimpleXMLElement as a web object
class xml_webobject extends magic_object  {

	//! construct with simpleXML
	public function __construct( $simpleXML ) {
	
		// throw exception if not simple XML
		if ( ! get_class( $simpleXML) == 'SimpleXMLElement')
			throw new IllegalArgumentException("player() should be instantiated with a SimpleXMLElement");
			
		// save xml
		$this->simpleXML = $simpleXML;
	}

	// magic get
	public function __get( $what ) {
	
		
	
		// get an xml attr
		if ( preg_match( '/xmlattr_([a-z]+)/', $what, $matches)) {
			$attrname = $matches[1];
			return $this->simpleXML[$attrname];
		} else
		switch( $what ) {
			default:
				return parent::__get( $what );
			break;
		}
	}
	
	//! magic set
	public function __set( $what , $how ){
		parent::__set( $what, $how );
	}

}

?>