<?php
/*! proposed class with convenient static methods to de-abstract database values.
 * 
 * Project:			Platform -- a PHP/MySQL/javascript/css framework
 * Module:			classes/DataAbstraction
 * 
 * Purpose:			Provide static methods to abstract and de-abstract data
 * 
 * Created by:		David Owen Greenberg <david.o.greenberg@gmail.com>
 * On:				15 Oct 2010
 */
 
final class DataAbstraction {
	
	//! constants for types of abstraction
	const DATETIME = 1;
	const TIME = 2;
	const TEXT = 3;
	const T_BOOLEAN = 4;
	const CURRENCY = 5;
	
	//! default date format
	const DEFAULT_DATE_FORMAT = 'M d, Y';
	const DEFAULT_TIME_FORMAT = 'h:i';
	
	//! edit classes as an array
	private static $editClasses = array ( self::DATETIME => 'InputElement' ,
		self::TIME => 'InputElement', self::TEXT => 'ManagedTextAreaElement');
	/*
	 * displayableFieldname( $Fieldname ): de-abstracts the fieldname for display
	 * @Fieldname: the original field name
	 * @returns: fieldname, de-abstracted for display
	 */
	 public static function displayableFieldname( $Fieldname ) {
	 
	 	// strip away underscores
	 	return preg_replace( '/[_]/' , ' ' , $Fieldname);
	 	
	 }
	 
	 //! re-abstract a de-abstracted value
	 public static function reAbstract( $type, $value ) {
		
		switch ( $type ) {
		
			case self::CURRENCY:
				
				return preg_replace( '/[$,]/' , '', $value );
			
			break;
			default:
				// no changes
				return $value;
			break;
		
		}
	}
	 
	 //! de abstract a given value as a particular type
	 public static function deAbstractas( $type, $value ) {
	 
		switch ( $type ) {
		
			case self::T_BOOLEAN:
				return ($value ? 'yes' : 'no');
			break;
			case self::DATETIME:
				return( date( self::DEFAULT_DATE_FORMAT , $value));
			break;
			case self::TIME:
				$hour = floor ( $value / 60);
				$minute = $value % 60;
				return $hour . ':' . sprintf("%02d",$minute) ;
			break;
			
			// for currency, add back the dollar sign, and commas
			case self::CURRENCY:
				return Currency::formatAs( Currency::DOLLAR, $value);
			break;
			
			default:
				return $value;
			break;
		}
	
	 
	 }
	 
	 //! returns HTML object for a given edit field type
	 public static function editFieldFor( $type ) {
		return isset( self::$editClasses[$type] ) ? self::$editClasses[$type] : 'InputElement' ;
	 }
	 
	
}
 
?>
