<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 10/03/13
 * Time: 10:50 PM
 */
class xo_url {
    private $valid = false;
    public function __construct($url=''){
        $url = strtolower($url);

        $regex =
            '/^((https?):\/\/){1}'.                                         // protocol
            '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
            '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
            '@)?(?#'.                                                  // auth requires @
            ')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
            '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
            '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
            '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
            ')(:\d+)?'.                                                // port
            ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
            '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
            '?)?)?'.                                                   // path and query string optional
            '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
            '$/i';
        if( preg_match($regex,$url))
            $this->valid = true;
    }
    public function is_valid(){
        return $this->valid;
    }
}
