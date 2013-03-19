<?php
/**
 *
 * Generates a UUID and returns in a form that the object may be printed
 *
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 19/03/13
 * Time: 08:14 AM
 */

class xo_uuid {
    private $uuid = '';
    public function __construct() {
        $this->uuid =
            sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff));
    }
    public function __toString(){
        return $this->uuid;
    }
}