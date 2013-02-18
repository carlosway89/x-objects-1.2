<?php

// get version information
require_once( "system/version.php");

// get the xobjects root
preg_match( '/^(.*)xobjects\.php$/',$argv[0],$matches);
$root = $matches[1];

// send a welcome message
echo "x-objects version $xobjects_version core script\r\n";

// get the user command
$cmd = @$argv[1];

// die if no command, or not recognized
if ( ! $cmd || ! function_exists($cmd) ) die( "usage: xobjects [command] [args]\r\n\r\ncommands: webapp\r\n");

// run the command
$cmd( $argv[2]);

// create a new webapp with a given name
function webapp( $name ){
	$cwd = getcwd();
	global $root;
	$dirs = array(
        "css",
        "js",
        "images" ,
        "app",
        "app/classes",
        "app/controllers",
        "app/views",
        "app/views/layouts",
        "app/views/pages",
        "app/models",
        "app/xml"
    );
	
	echo "creating webapp $name in $cwd\r\n";
	if ( file_exists( $name)){
		if ( ! is_dir( $name )){
			die("$name is not a directory!\r\n");
		} else {
		}
	} else {
		mkdir( $name);
		
	}
	
	foreach( $dirs as $dir)
		create_dir( "$name/$dir");
		
	// now copy over index file, translating vars
	copy_and_replace( "$root/templates/index.newwebapp.php", "$cwd/$name/index.php",
		array( 
			"/\_xobjects\_root\_/" => $root,
			"/\_webapp\_root\_/" => "$cwd/$name",
		
		));
		
	// copy over xml config, translating vars
	copy_and_replace( "$root/templates/x-objects.xml", "$cwd/$name/app/xml/x-objects.xml",
		array( 
			"/\_appname\_/" => $name,
		
		));
	
	// copy over some controllers
    copy( "$root/templates/controllers/page.php","$cwd/$name/app/controllers/page.php");
    copy( "$root/templates/controllers/home.php","$cwd/$name/app/controllers/home.php");

    // default template
    copy_and_replace( "$root/templates/views/layouts/default.php", "$cwd/$name/app/views/layouts/default.php",
        array(
            "/\_appname\_/" => $name,

        ));

    // page views
    copy("$root/templates/views/pages/e404.php","$cwd/$name/app/views/pages/e404.php" );
    copy("$root/templates/views/pages/home.php","$cwd/$name/app/views/pages/home.php" );

    // default template
    copy_and_replace( "$root/templates/js/app-jquery.js", "$cwd/$name/js/$name.js",
        array(
            "/\_appname\_/" => $name,
        ));
    /* js
    copy("$root/js/script.js","$cwd/$name/js/script.js" );
    copy("$root/js/jquery.js","$cwd/$name/js/jquery.js" );
    copy("$root/js/jquery.x-objects.js","$cwd/$name/js/jquery.x-objects.js" );
*/
    // default template
    copy_and_replace( "$root/templates/classes/service.php", "$cwd/$name/app/classes/$name.php",
        array(
            "/\_appname\_/" => $name,
        ));
    copy("$root/templates/misc/htaccess.txt","$cwd/$name/.htaccess" );

    echo "done\r\n";
	
}

function copy_and_replace($source, $dest, $subs){
	$in = fopen ( $source, "r");
	$out = fopen( $dest, "w");
	// read in xml as a string
	while ( $data = fgets( $in ) ) {
		// replace app name
		foreach( $subs as $reg => $rep )
			$data = preg_replace( $reg , $rep , $data );
		// save it
		fputs( $out, $data );
	}
	fclose( $in);
	fclose( $out);
}
	

// create a directory
function create_dir( $name){
	if ( file_exists( $name)){
		if ( ! is_dir( $name )){
			die("$name is not a directory!\r\n");
		} else {
		}
	} else {
		mkdir( $name);
		
	}
	
}

?>
