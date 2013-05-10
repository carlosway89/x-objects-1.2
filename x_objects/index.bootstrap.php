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
try {
    $bootstrap = new xo_index_bootstrap();
// run it
    $bootstrap->go();
} catch (Exception $e){
    echo $e->getMessage();
}


    if ( $container->debug ) echo "$tag->event_format: done rendering page, ready to shut down<br>";
    // explicitly destroy the container
    if ( $container) $container->destroy();

?>