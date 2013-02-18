<?php
/** This is a special class to easily manage REQUEST super global as a PHP Object
 * 
 * Example:
 * $req = new REQUEST
 * to access $_POST['name']:
 * $name = $req->name;
 * to access $_GET['value']:
 * $val = $req->value;
 * 
 * To check if a value is set:
 * if ( $req->is_set("foo"))
 *
 * To get a value quickly in one line:
 * $val = REQUEST::create()->val;
 *
 * @copyright This Class is Open Source for any use that does not violate laws of commerce
 * 
 * @author David Owen Greenberg <david.o.greenberg@gmail.com>
 *
 * @property $string string representation of request
 */
final class REQUEST {

    public function __construct(){
       // echo "new request";
    }
	//! magic get for request vars
	public function __get( $var ) { 
		switch( $var ) {
            case 'as_array':
                return $_REQUEST;
            break;
			case 'string':
				$str = '';
				foreach( $_REQUEST as $name => $value )
					$str .= "$name=$value,";
				return $str;
			break;
			default:
				return @$_REQUEST[$var];
			break;
		}
	}

	//! check if a value is set
	public function is_set( $name ){
		return isset( $_REQUEST[$name]);
	}
	
	//! create one
	public static function create(){ return new REQUEST(); }
	
	
	public function __toString(){
		$str = "";
		foreach ( $_REQUEST as $n => $v) $str .= "$n=$v,";
		return $str;
	}
}

?>