<?php
/**
 * Base script to install a new X-Objects web or mobile app
 *
 * Example usage (from the Unix shell):
 *
 * % cd /var/www
 * % php /var/www/x_objects/xobjects.php webapp /var/www/myapp
 */

// bootstrap x-objects
require_once( "include/bootstrap.xobjects.php");
// grab container
$container = x_objects::instance();

// get the user command
$cmd = @$argv[1];

// die if no command, or not recognized
if ( ! $cmd  ) die( "usage: xobjects [command] [args]\r\n\r\ncommands: webapp\r\n");

// get the xobjects root
preg_match( '/^(.*)xobjects\.php$/',$argv[0],$matches);
$root = $matches[1];

$installer = new xo_installer();
$installer->$cmd( $argv[2], $root);


?>
