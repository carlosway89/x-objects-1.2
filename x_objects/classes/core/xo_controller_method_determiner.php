<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 17/02/13
 * Time: 05:29 PM
 */
class xo_controller_method_determiner {
    public function __construct(){
        $method = "default_action";
        if ( isset($url_parts[2]) && $url_parts[2]!="")
            $method = $url_parts[2];
        elseif ($uri->part(2)!= "")
            $method = $uri->part(2);

    }
}
