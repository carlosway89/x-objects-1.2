<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 06/02/13
 * Time: 10:42 AM
 */
class xo_secure_filter {
    private $data = "";

    public function __construct($data) {
        $data = trim(htmlentities(strip_tags($data)));
        if (get_magic_quotes_gpc())
            $data = stripslashes($data);
        //$data = @mysql_real_escape_string($data);
        $this->data = $data;
    }
    public function __toString(){
        return $this->data;
    }

}
