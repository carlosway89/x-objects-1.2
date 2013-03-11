<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 10/03/13
 * Time: 10:50 PM
 */
class xo_url {
    private $valid = false;
    public function __construct($url=''){
        $url = strtolower($url);
        echo $url;
        $regex = "((https?|ftp):\/\/)?";
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
        $regex .= "([a-z0-9-.]*).([a-z]{2,3})";
        $regex .= "(:[0-9]{2,5})?";
        $regex .= "(\/([a-z0-9+\$_-].?)+)*\/?";
        $regex .= "(?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_\.-]*)?";
        $regex .= "(#[a-z_.-][a-z0-9+\$_\.-]*)?";
        if( preg_match("/^$regex$/",
            $url))
            $this->valid = true;
    }
    public function is_valid(){
        return $this->valid;
    }
}
