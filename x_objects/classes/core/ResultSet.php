<?php
//! stores a result set from a search, as an array of objects
abstract class ResultSet implements DisplayableObject {

	//! display mode
	private static $displayMode = DisplayableObject::DISPLAY_SEARCH_DEFAULT;
	
	//! stores the results
	private $results = array();

	//! stores number of results
	protected $numresults = 0;
	
	//! stores the type of results found, as names of objects
	protected $resultTypes = array();
	
	//! add more results to the set
	public function add( $objArray ) {
	
		// add the results
		if ( is_array( $objArray ) ) 
			foreach ( $objArray as $obj )
				array_push( $this->results, $obj);
		else array_push( $this->results, $objArray );
		
		// bump the counter
		$this->numresults += count( $objArray );
		
		// store the type
		$keys = array_keys( $objArray );
		
		if ( isset( $keys[0])) {
			$class = get_class( $objArray[$keys[0]] );
			$this->resultTypes[$class] = true;
		} 
	}
	
	//! does the result set have a particular type?
	public function hasType( $type ) { return isset( $this->resultTypes[$type] ) ? true : false; }
	
	//! get results of a specific type
	public function getType( $type ) {
	
		$subset = array();
		
		foreach ( $this->results as $result )
			if ( get_class( $result ) == $type )
				array_push( $subset, $result );
				
		return $subset;
	}
	
	//! sets the display mode
	public static function setDisplayMode( $mode ) { self::$displayMode = $mode; }
	
	//! gets the display mode
	public static function getDisplayMode() { return self::$displayMode; }
}
?>