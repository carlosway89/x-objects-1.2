<?php

/*! xCache is a dynamic repository used by any class in the system to boost performance by saving
 *  objects and members that are repeatedly accessed
 */
class xCache {

	//! the cache
	private static $cache = array();

	//! set a member in the cache
	public static function set( $key, $object ) {
	
		self::$cache [ $key ] = $object;
		
		// for command chaining!
		return $object;
	
	}
	
	//! get a member of the cache
	public static function get( $key ) {
	
		return isset( self::$cache[ $key ] ) ? self::$cache[ $key ] : null;
		
	}
	
	//! does a member exist in the cache?
	public static function exists( $key ) {
	
		return isset( self::$cache[ $key ] );
		
	}
	
}

?>