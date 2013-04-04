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
final class REQUEST_URI {

	public function part( $num){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"none","none");
        /**
         * we used to explode the request uri but this won't work, instead
         * we need to explode the parsed url parts
         */
        //$parts = explode('/', $_SERVER['REQUEST_URI']);
        $parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
// now get the parts
        $parts = explode('/',$parsed_url['path']);

        $part = isset( $parts[(int)$num])?preg_replace( '/%20/',' ', $parts[ (int)$num ]):null;
		if ( $container->debug && $container->debug_level > 4)
			echo "$tag->event_format : part = $part<br>\r\n";
		return $part;
	}
    public static function create(){ $c = __CLASS__; return new $c(); }
    public function __toString(){
        $str = '';
        $parts = explode('/', $_SERVER['REQUEST_URI']);
        foreach ( $parts as $part) $str .= $part. ",";
        return $str;
    }
}

?>