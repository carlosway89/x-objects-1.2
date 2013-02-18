<?php 
/*
 * Project:					PlatformPHP
 * Module:					Debugger
 * Version:					1.0
 * ModDate:					Sept 30, 2010
 * Author:					David Owen Greenberg
 * Descr/Purpose:			Simple module for creating debugging messages
 */

final class Debugger {

	//! indicates whether debugging is enabled or disabled
	private static $enabled = false;

	/*
	 * echoMessage( $Message )
	 */	
	public static function echoMessage( $Message ) {
	
		echo '<br><span class="Debugger"> (DEBUG) ' . $Message . '</span><br>' . "\r\n";
		
	}
	
	//! enable debugging
	public static function enable() { self::$enabled = true; }
	
	//! disable debugging
	public static function disable() { self::$enabled = false; }
	
	//! is debugging enabled
	public static function enabled() { return self::$enabled; }

}

?>