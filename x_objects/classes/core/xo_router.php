<?php
/**
 *
 * Abstract Class to help define a custom Router for applications
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 13/03/13
 * Time: 11:33 AM
 */

abstract class xo_router {
    protected $key = null;
    public function __construct($key){
        $this->key = $key;
    }
    /**
     * Return a custom route
     * @param $key string original route requested by user
     * @return string route, or null if none
     */
    abstract public function route($key = null);
    /**
     * Return controller filename for custom route
     * @param $key string original route requested by user
     * @return string filename, or null if none
     */
    abstract public function controller_file($key = null);
    /**
     * Return controller method for custom route
     * @param $key string original route requested by user
     * @return string method, or null if none
     */
    abstract public function controller_method($key = null);

}