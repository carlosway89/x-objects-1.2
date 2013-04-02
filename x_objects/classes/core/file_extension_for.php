<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 12/02/13
 * Time: 05:05 PM
 */
class file_extension_for {
    private $extension = "";
    public function __construct($filename){
        if ( preg_match( '/(.+)\.([a-z|A-Z|0-9|_]+)/',$filename,$hits)){
            $this->extension = $hits[2];
        } else {
            $this->extension = 'unknown';
        }
    }
    public function __toString(){
        return $this->extension;
    }
}
