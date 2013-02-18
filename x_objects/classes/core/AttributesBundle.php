<?php

//! an attributes bundle represents a bundle of name-value attributes (e.g. name="a" id="b" etc)
class AttributesBundle extends AttributedObject {

	//! when constructing, parse out the attributes list into members
	public function __construct( $attributesList ) {
	
		// split into pairs
		$pairs = explode (',',$attributesList );
		
		// set each
		foreach ( $pairs as $pair ) {
			$nvpair = explode( '=' , $pair );			
			$this->set( $nvpair[0] , $nvpair[1] );
		}
	}
	
	//! returns all attributes bundled
	public function bundled() {
	
		// save the bundle as a string
		$bundle = '';
		
		// we don't put a comma before the first one
		$first = true;
		
		foreach ( $this->attributes() as $name => $value ) {
			$bundle .= (( ! $first ) ? ',' : '') . "$name=$value";
			$first = false;
		}
		
		// return the bundle
		return $bundle;
		
	}
	
}
?>