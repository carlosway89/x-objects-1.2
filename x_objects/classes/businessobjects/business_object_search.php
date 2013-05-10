<?php
/**
 *
 * Object representation of a search string for loading a Business Object
 *
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 08/05/13
 * Time: 02:05 PM
 */

class business_object_search {
    private $str = '';
    public function __construct($str = null){
        $this->str = $str;
        $this->check();
    }
    private function check(){
        if ( $this->str){
            $count = strlen($this->str) - strlen(preg_replace('/\'/','',$this->str));
            if ( $count % 2 == 1)
                throw new IllegalArgumentException("$this->str: Malformed Search, has an odd number of 's");
        }
    }
}