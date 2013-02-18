<?php
session_start();

// bootstrap x-objects
require_once( 'x_objects/include/bootstrap.php' );

// create a tag
$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"main","main");

// get container
try {
	$container = x_objects::instance();
} catch ( Exception $e ) {
	echo $e->getMessage();
}

// get webroot and pathroot
global $webroot, $pathroot, $directory_name;

// set controller 
$key = isset( $_REQUEST['key'] ) && $_REQUEST['key'] != '' ? $_REQUEST['key'] : 'home';

// default page title
$page_title = ucfirst( $key );

// logged in class
$logged_in_class = $container->logged_in() ? 'logged-in' : 'not-logged-in';

$app_name = $container->appname;

// include controller code
$controllers = array(
	$pathroot . "/controllers/$key.php",
	$pathroot . $directory_name . "/controllers/$key.php"
);
if ( file_exists( $controllers[0] ) )
	require_once( $controllers[0] );
elseif ( file_exists( $controllers[1] ))
	require_once( $controllers[1] );
else $missing_controller = "It appears the controller for $key is missing.  Please make sure the file $controllers[0] or $controllers[1] exists, if not
please create it.";


// render page
try {

	// obtain a handle to the application container
	// load a specific app and display a specific page
	if ( ! @$bypass_view) echo $container->app->page( $key )->xhtml();
		
// handle any exceptions
} catch ( Exception $e ) {
	// log exception
	$container->log(
		xevent::exception,
		"$tag->event_format : " . $e->getMessage()
		);

	echo '<div class="xo-round5 exception" style="padding: 5px; width: 800px; height: auto; overflow: hidden; color: blue; font-weight: bold; margin: 10px auto; border: 1px blue solid">' . $e->getMessage() . '</div>';
	
}
?>