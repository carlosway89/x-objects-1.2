<?php
/**
 * User: "David Owen Greenberg" <david@reality-magic.com>
 * Date: 23/04/13
 * Time: 10:49 AM
 */

class xo_controller_manager {
    private $key = null;
    private $appname = null;
    private $router = null;
    private $controller_file = null;
    public function __construct($key,$appname){
        $this->key = $key;
        $this->appname = $appname;
    }
    public function load_controller(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $controller_class = $this->find_controller_class();
        if ($container->debug) echo "$tag->event_format: found controller class $controller_class<br>";
        if ( $controller_class ){
            // fetch the class
            $this->fetch_controller_class($controller_class);
            global $autoload_bypass_exception;
            $autoload_bypass_exception = true;

            $controller = new $controller_class;
            $method = $this->controller_method();
            if ( method_exists($controller,$method))
                $controller->$method();
        }
        $autoload_bypass_exception = false;
    }
    private function controller_method(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        $method = null;
        if ( $this->router)
            $method = $this->router->controller_method_for($this->key);
        if ( ! $method){
            $parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
            // now get the parts
            $url_parts = explode('/',$parsed_url['path']);
            $method = strlen($url_parts[2])?$url_parts[2]:'default_action';
        }
        if ($container->debug) echo "$tag->event_format: method is $method<br>";
        return $method;
    }
    private function find_controller_class(){
        $class = null;
        global $container;
        global $autoload_bypass_exception;
        $autoload_bypass_exception = true;

        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        // first see if we have a custom router
        $router_class = $this->appname."_router";
        //$route = "";
        if ( class_exists($router_class)){
            if ( $container->debug ) echo "$tag->event_format: found a custom router<br>\r\n";
            $this->router = new $router_class();
            // set class from router
            $this->controller_file = (string)$this->router->controller_file_for($this->key);
            $class = $this->controller_file?"$this->controller_file"."_controller":null;
        }
        if ( ! $class) {
            $parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
            // now get the parts
            $url_parts = explode('/',$parsed_url['path']);
            $this->controller_file = $url_parts[1]?$url_parts[1]:'home';
            $class = $url_parts[1]?$url_parts[1]."_controller":'home_controller';
        }
        $autoload_bypass_exception = false;

        return $class;
    }
    private function fetch_controller_class($class){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        global $webapp_location,$xobjects_location;
        $file = $webapp_location. "/app/controllers/$this->controller_file.php";
        if ( $container->debug ) echo "$tag->event_format: controller file is $file<br>";
        if ( file_exists( $file))
            require_once( $file);
        elseif ( file_exists( $xobjects_location. "controllers/$this->controller_file.php"))
            require_once( $xobjects_location. "controller/$this->controller_file.php");
    }
}