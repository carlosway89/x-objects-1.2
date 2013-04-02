<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 20/12/12
 * Time: 11:29 AM
 */
class image_typer {
    private $str = "";
    public function __construct($string){
        $this->str = $string;
    }
    public function __toString(){
        $str = "unknown";
        if ( preg_match('/\.png/',strtolower($this->str)))
            $str = "png";
        if ( preg_match('/\.jpg/',strtolower($this->str)))
            $str = "jpg";
        if ( preg_match('/\.gif/',strtolower($this->str)))
            $str = "gif";
        return $str;
    }
}
