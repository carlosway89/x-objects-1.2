<?php
/** special class to easily manage REQUEST super global
 *
 * @author David Owen Greenberg <david.o.greenberg@gmail.com>
 *
 * @property $string string representation of request
 */
class SESSION {

	//! magic get for request vars
	public function __get( $var ) { 
		switch( $var ) {
			case 'string':
				$str = '';
				foreach( $_SESSION as $name => $value )
					$str .= "$name=$value,";
				return $str;
			break;
			default:
				return @$_SESSION[$var];
			break;
		}
	}
	
	//! magic set
	public function __set($what, $how){
		$_SESSION[$what]=$how;
	}
	public function members(){ return $_SESSION;}
	//! create one
	public static function create(){ return new SESSION(); }

    public function __toString(){
        try {
            $str = (string) new xo_array($_SESSION);
        } catch (Exception $e){
            $str = $e->getMessage();
        }
        return $str;
    }
}

?>