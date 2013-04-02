<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 06/02/13
 * Time: 10:48 AM
 */
class xo_security_agent {
    public function session_hijacked(){
        $s = new SESSION();
        return ($s->HTTP_USER_AGENT != md5($_SERVER['HTTP_USER_AGENT']))?true:false;
    }
}
