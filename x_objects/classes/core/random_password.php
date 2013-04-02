<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 06/01/13
 * Time: 11:55 PM
 */
class random_password {
    private $length = 1;
    public function __construct($length){
        $this->length = $length;
    }
    public function __toString() {
        // the character set to create the password
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        // initialize a randomized number
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        // ( you can tell a C programmer wrote this code :-)
        while ($i < $this->length) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
}
