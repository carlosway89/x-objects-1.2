<?php
@session_start();
/**
 * Project: X-Objects MVC framework for PHP and jQuery
 * Version: 1.2.x
 * Author: <david@reality-magic.com> David Owen Greenberg
 * Module: Bootstrap
 * Component: Index Bootstrap File
 *
 * This file is called to load up any page or view in the application.
 */

final class xo_index_bootstrap{
    private $bypass_view = false;
    private $start_time; // start timer
    private $start_mem;
    private $req,$ses,$cookie,$uri,$parsed_url,$url_parts;
    private $key;
    private $app_name;

    public function __construct(){
        $this->start_time = microtime(true);
        $this->start_mem = memory_get_usage();
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        // get cookies, session and request
        $this->req = new REQUEST();
        $this->ses = new SESSION();
        $this->cookie = new COOKIE();
        $this->uri = new REQUEST_URI();
        $this->parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
        // now get the parts
        $this->url_parts = explode('/',$this->parsed_url['path']);
        $this->key = (string) new xo_controller_key();

    }
    public function go(){
        global $container;
        global $webapp_location, $xobjects_location,$pathroot, $directory_name;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        try { $container = x_objects::instance(); } catch ( Exception $e ) {
            echo '<span style="color:red;">X-Objects bootup failed: '.$e->getMessage().'</span>';
        }
        if ( $container->debug ) echo "$tag->event_format: key is $this->key<br>";
        $this->app_name = $container->appname;
        $manager = new xo_controller_manager($this->key,$this->app_name);
        $manager->load_controller();
        if ( $container->debug && $container->debug_level >= 5) echo "$tag->event_format: container SINGLETON created<br>\r\n";

   }
}

// bootstrap file
$container = null;
// get webroot and pathroot
global $webroot, $pathroot, $directory_name,$xobjects_location;

// load global functions
require_once( $xobjects_location."include/xo_functions.php");

// create the bootstrap file
$bootstrap = new xo_index_bootstrap();
// run it
$bootstrap->go();

/*
    if ( file_exists( $controllers[1] )){
        require_once( $controllers[1] );
        $class = $key."_controller";
        $controller = new $class;
        $method = isset($url_parts[2])?$url_parts[2]: ($uri->part(2)?$uri->part(2):"default_action");
        if ( $container->debug) echo "$tag->event_format: called method was $method<br>\r\n";
        try {
            $controller->$method();
        } catch (Exception $e){
            $exception_span = '<div style="position: absolute;z-index:100;margin: 5px;padding: 10px; font-family: verdana,sans-serif;width:360px;height:auto;background-color: #1f1f1f;color:white !important">';
            $exception_span .= "<h1>X-Objects says (1):</h1><p>Something isn't quite right here:</p>";
            echo $exception_span. "<p>".$e->getMessage(). "</p></div>";
        }
    }

    elseif ( file_exists( $controllers[0] ) ){
	    if ( $container->debug ) echo "$tag->event_format : found a controller $controllers[0]<br>\r\n";
	    try {
		    require_once( $controllers[0] );
		    $class = $key."_controller";
		    $controller = new $class;
            // get the method
           // $method = (string) new xo_controller_method_determiner();
		    $method = "default_action";
            if ( isset($url_parts[2]) && $url_parts[2]!="")
                $method = $url_parts[2];
            elseif ($uri->part(2)!= "")
                $method = $uri->part(2);
            if ( $container->debug) echo "$tag->event_format: called method was $method<br>\r\n";
		    $controller->$method();
	    } catch ( Exception $e){
            $exception_span = '<div style="position: absolute;z-index:100;margin: 5px;padding: 10px; font-family: verdana,sans-serif;width:360px;height:auto;background-color: #1f1f1f;color:white !important">';
            $exception_span .= "<h1>X-Objects says (1):</h1><p>Something isn't quite right here:</p>";
		    echo $exception_span. "<p>".$e->getMessage(). "</p></div>";
	    }
    }
    elseif ( isset( $controllers['routed']) && $controllers['routed'] && file_exists($controllers['routed'])){
        if ( $container->debug ) echo "$tag->event_format: found a controller from custom route<br>\r\n";
        try {
            require_once( $controllers['routed'] );
            $class = $route."_controller";
            $controller = new $class;
            $method = "default_action";
            if ( $container->debug) echo "$tag->event_format: URL Parts are ".(string) new xo_array( $url_parts)."<br>\r\n";
            if ( $container->debug) echo "$tag->event_format: URI is $uri<br>\r\n";
            if ( isset($url_parts[1]) && $url_parts[1]!="")
                $method = $url_parts[1];
            elseif ($uri->part(2)!= "")
                $method = $uri->part(2);
            else {
                if ( $container->debug) echo "$tag->event_format: NO explicit method found for this controller,fallback to default<br>\r\n";

            }

            if ( $container->debug) echo "$tag->event_format: called method was $method<br>\r\n";
            $controller->$method();
        } catch ( Exception $e){
            $exception_span = '<div style="position: absolute;z-index:100;margin: 50px auto 0;padding: 10px; font-family: verdana,sans-serif;width:750px;height:auto;background-color: #1f1f1f;color:white !important">';
            $exception_span .= "<h1>Argh!  An Exception was tossed yer way!</h1><p>Drat!  Just when we thought everything was ready to go!</p>";
            echo $exception_span. "<p>".$e->getMessage(). "</p></div>";
        }
    } else {
	    $r = (string) $container->xml->site->controllers->missing_redirect;
	    if ( $r ){
		    $p = explode('/',$r);
		    if ( file_exists( $webapp_location."/app/controllers/$p[0].php")) {
			    require_once( $webapp_location."/app/controllers/$p[0].php");
			    $class = $p[0]."_controller";
			    $controller = new $class;
			    $method = $p[1];
			    if ( ! method_exists( $controller, $method))
				    die("Bad request: missing controller redirect $r no such method");
			    else
				    $controller->$method();
		    } else {
		    	die( "Bad request: missing controller redirect $r does not exist");
		    }
	    } else {
		    $msg = "It appears the controller for $key is missing.  Please make sure the file $controllers[0] or $controllers[1] exists, if not
		    please create it.";
        }
    }
    if ( $container->debug ) echo "$tag->event_format: done loading controller, ready to pull view<br>";
    // include views code
    $view = $pathroot . "/views/$key.php";
    if ( file_exists( $view ) )
	    require_once( $view );
*/
    if ( $container->debug ) echo "$tag->event_format: done rendering page, ready to shut down<br>";
    // explicitly destroy the container
    if ( $container) $container->destroy();

?>