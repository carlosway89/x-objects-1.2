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
    /**
     * Return a custom route
     * @param $key string original route requested by user
     * @return string route, or null if none
     */
    abstract public function route_for($key);
    /**
     * Return controller filename for custom route
     * @param $key string original route requested by user
     * @return string filename, or null if none
     */
    abstract public function controller_file_for($key);
    /**
     * Return controller method for custom route
     * @param $key string original route requested by user
     * @return string method, or null if none
     */
    abstract public function controller_method_for($key);

}