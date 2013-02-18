<?php
/**
 * This is the default index.php file for your new web application.
 * 
 * In most cases, you will not need to edit this file.
 * 
 * However, for some configurations, it may be necessary to tweak it slightly in order 
 * for the application to run properly
 */
  
// set the x_objects directory location:
$xobjects_location = "_xobjects_root_";
 
// set your webapps directory location
$webapp_location = "_webapp_root_"; 
 
// bootstrap x-objects
require_once( "$xobjects_location"."include/bootstrap.xobjects.php" );

// run index bootstrap file
require_once( "$xobjects_location" . "index.bootstrap.php");

?>
