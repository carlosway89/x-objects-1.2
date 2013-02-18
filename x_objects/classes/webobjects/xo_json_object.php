<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 01/02/13
 * Time: 04:24 PM
 */
class xo_json_object {
    protected $json_result = array(
        "signature"=>"xo_json_object"
    );
    public function __toString(){
        return (string)json_encode($this->json_result);
    }
}
