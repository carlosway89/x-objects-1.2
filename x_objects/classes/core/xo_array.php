<?php
/**
 * An xo_array is a convenience class that creates a printable object version of an array
 * User: "David Owen Greenberg <code@x-objects.org>
 * Date: 04/10/12
 * Time: 03:27 PM
 */
class xo_array extends magic_object{
    private $a,$d;
    public function __construct( $a , $d = 1 ){
        $this->a = $a;
        $this->d = $d;
    }

    public function __get( $what ){
        return is_array($this->a) && isset( $this->a[$what])?$this->a[$what]:null;
    }
    public function __toString(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        $s = "array(";
        if ( is_array($this->a))
            foreach( $this->a as $n=>$v){
            if ( is_array($v))
                $s .= "$n = '".new xo_array($v). "', ";
            else
            {

                $s .= "$n = '$v'";
                if ($this->d == 2) $s .= "<br>\r\n";
                else $s.=', ';
            }
        } else {
            if ( $container->debug) echo "$tag->event_format: $this->a is NOT an array<br>\r\n";
        }
        $s .= ")";
        return $s;
    }
}
