<?php
/**
 * An xo_string is a convenience class that creates a printable object version of a standard object
 * User: "David Owen Greenberg <owen.david.us@gmail.com>
 * Date: 11/10/12
 * Time: 03:27 PM
 */
class xo_string extends magic_object{
    public function __construct( $o ){
        $this->o = $o;
    }
    public function __toString(){
        $s = "Object(";
        foreach( $this->o as $n=>$v){
            $value = is_object($v)?new xo_string($v):$v;
            $s .= "$n = '$value', ";
        }
        $s .= ")";
        return $s;
    }
}
