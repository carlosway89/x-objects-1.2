<?php
/**
 * Project: X-Objects MVC framework for PHP and jQuery
 * Version: 1.2.x
 * Author: <david@reality-magic.com> David Owen Greenberg
 * Module: Bootstrap
 * Component: Index Bootstrap File
 *
 * This file is called to load up any page or view in the application.
 */
@session_start();
$bypass_view = false;
// start timer
$x_start_time = microtime(true);
// start memory
$x_start_mem = memory_get_usage();

// create a tag
$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"main","main");

$container = null;
try { $container = x_objects::instance(); } catch ( Exception $e ) {
	echo '<span style="color:red;">X-Objects bootup failed: '.$e->getMessage().'</span>';
} 
if ( $container->debug ) echo "$tag->event_format: container SINGLETON created<br>\r\n";

// set debugging
$debug = Debugger::enabled();	

// get cookies, session and request
$req = new REQUEST();
$ses = new SESSION();
$cookie = new COOKIE();
$uri = new REQUEST_URI();
$parsed_url = parse_url( "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
// now get the parts
$url_parts = explode('/',$parsed_url['path']);


// get webroot and pathroot
global $webroot, $pathroot, $directory_name,$xobjects_location;

// load global functions
require_once( $xobjects_location."include/xo_functions.php");

// api call?
if ( preg_match( '/^\/api/', $_SERVER['REQUEST_URI'] )){
	require_once( $pathroot . $directory_name . "/api/api.php");
} else {

    /**
     * get the controller key
     */
    $key = (string) new xo_controller_key();

    $app_name = $container->appname;
    global $webapp_location, $xobjects_location,$pathroot, $directory_name;
    // include the global controller if defined
    if ( file_exists( $webapp_location."/app/controllers/global.php"))
	    require_once( $webapp_location."/app/controllers/global.php");
    else {
	    // is auto code generation enabled?
	    if ( @$container->config->code_generation->missing->controllers == "yes" ){
		    if ( copy( $pathroot.$directory_name."/templates/controller.php", $webapp_location."/app/controllers/global.php"))
			    require_once( $webapp_location."/app/controllers/global.php");
	    }
		
    }
    // include controller code
    $controllers = array(
		$webapp_location. "/app/controllers/$key.php",
		$xobjects_location . "controllers/$key.php"
    );
    // check for a specific route
    $router_class = $container->appname."_router";
    global $autoload_bypass_exception;
    $autoload_bypass_exception = true;
    $route = "";
    if ( class_exists($router_class)){
        if ( $container->debug ) echo "$tag->event_format: found a custom router<br>\r\n";
        $router = new $router_class();
        $route = $router->route_for($key);
        if ( $container->debug ) echo "$tag->event_format: found a custom route: $route<br>\r\n";
        $controllers['routed'] = $route? $webapp_location."/app/controllers/$route.php":null;
    }
    $autoload_bypass_exception = false;
    if ( $container->debug ) echo "$tag->event_format: done with prelimaries, ready to load controller<br>\r\n";
    if ( $container->debug ) echo "$tag->event_format: searching for controllers in ".new xo_array( $controllers)."<br>\r\n";

    if ( file_exists( $controllers[1] )){
        require_once( $controllers[1] );
        $class = $key."_controller";
        $controller = new $class;
        $method = isset($url_parts[2])?$url_parts[2]: ($uri->part(2)?$uri->part(2):"default_action");
        if ( $container->debug) echo "$tag->event_format: called method was $method<br>\r\n";
        $controller->$method();
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
}
    if ( $container->debug ) echo "$tag->event_format: done rendering page, ready to shut down<br>";
    // explicitly destroy the container
    if ( $container) $container->destroy();

?>