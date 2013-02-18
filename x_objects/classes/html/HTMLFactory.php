<?php

//! HTMLFactory contains static methods to construct dynamic html objects from other objects
class HTMLFactory {

	//!! convert an array of objects to an array of TR elements containing the objects
	public static function arrayOfTR( $objects ) {
	
		$array = array();
		
		foreach ( $objects as $object )
			array_push( $array, new TRElement('class=TRArray',$object));
			
		return $array;
	}
	
	//! create a new html element
	public static function create( $class, $attributes = null, $innerhtml = null ) {
	
		return new $class( $attributes, $innerhtml );
		
	}

}
?>