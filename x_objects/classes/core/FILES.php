<?php
/** This is a special class to easily manage FILES super global as a PHP Object
 * 
 * Example:
 * $f = new FILES;
 * to access $_FILES['key']['name']:
 * $name = $f->key->name;
 *
 *
 * @copyright This Class is Open Source for any use that does not violate laws of commerce
 * 
 * @author David Owen Greenberg <info@x-objects.org>
 *
 * @property $string string representation of file
 */
final class FILES {

	//! magic get for request vars
	public function __get( $var ) {
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        switch( $var ) {
            case 'as_array':
                return $_FILES;
            break;
			case 'string':
                return new xo_array($_FILES);
			break;
			default:
				return is_array( @$_FILES[$var])? new xo_array( @$_FILES[$var]):@$_FILES[$var];
			break;
		}
	}

	//! check if a value is set
	public function is_set( $name ){
		return isset( $_FILES[$name]);
	}
	
	//! create one
	public static function create(){ return new REQUEST(); }
	
	
	public function __toString(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $str = "";
		foreach ( $_FILES as $n => $v) {
            if ( is_array($v)){
                if ( $container->debug) echo "$tag->event_format: FILES member $n is an array<br>\r\n";
                $str .= "$n=".new xo_array($v);
            }
            else {
                if ( $container->debug) echo "$tag->event_format: FILES member $n is NOT an array<br>\r\n";
                $str .= "$n=$v,";
            }
        }
		return $str;
	}
}

?>