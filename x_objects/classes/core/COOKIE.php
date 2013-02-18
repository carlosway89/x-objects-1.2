<?php
/** special class to easily manage COOKIE super global
 *
 * @author David Owen Greenberg <david.o.greenberg@gmail.com>
 *
 * @property $string string representation of request
 */
class COOKIE {

	//! magic get for request vars
	public function __get( $var ) { 
		switch( $var ) {
			case 'string':
				$str = '';
				foreach( $_COOKIE as $name => $value )
					$str .= "$name=$value,";
				return $str;
			break;
			default:
				return @$_COOKIE[$var];
			break;
		}
	}
	
	//! magic set
	public function __set($what, $how){
		setcookie($what, $how);
	}
	public function members(){ return $_COOKIE;}
	//! create one
	public static function create(){ return new COOKIE(); }
	
}

?>