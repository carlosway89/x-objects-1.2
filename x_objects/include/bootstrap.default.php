<?php 

	// bootstrap xml
	$xml = simplexml_load_string(
		"<bootstrap>
			<pathroot>#pathroot</pathroot>
			<webroot>#webroot</webroot>
			<directory_name>x_objects</directory_name>
			<platform>#platform</platform>
		</bootstrap>");
	
	// connection xml
	$conn_xml = simplexml_load_string(
		"<connection>
			<database>#dbname</database>
			<host>#dbhost</host>
			<username>#dbuser</username>
			<password>#dbpass</password>
			<socket>#dbsocket</socket>
			<port>#dbport</port>
		</connection>"
	);
	
	
	// set the directory name
	$directory_name = (string) $xml->directory_name;

	// default separator
	$platform = (string) $xml->platform;
	$separator = $platform == 'win' ? ';' : ':';
		
	// include configuration and database connection files
	$pathroot = (string) $xml->pathroot;
	$webroot = (string) $xml->webroot;
	define ( 'PATHROOT' , $pathroot );
	
	//echo $pathroot;
	
	// set timezone
	define( 'TIMEZONE', 'America/Denver');
	if ( defined( 'TIMEZONE' ) && TIMEZONE != '' )
		if ( ! date_default_timezone_set( TIMEZONE ) )
			echo 'platform.bootstrap.php: unable to set timezone = ' . TIMEZONE;
	
	// function to obtain all possible paths to find classfiles
	function paths() {

		global $pathroot, $directory_name, $separator,$xml;
		
		$paths = $pathroot . "$directory_name/classes" .					// platform classes
			$separator . $pathroot . "$directory_name/classes/data" .					// data
			$separator . $pathroot . "$directory_name/classes/core" .					// core app services
			$separator . $pathroot . "$directory_name/classes/sql" .					// core app services
			$separator . $pathroot . "$directory_name/classes/behaviors" .				// interfaces specifying behaviors
			$separator . $pathroot . "$directory_name/classes/services" .				// service classes
			$separator . $pathroot . "$directory_name/classes/crm" .				// CRM classes
			$separator . $pathroot . "$directory_name/classes/webobjects" .				// web objects
			$separator . $pathroot . "$directory_name/classes/controls" .				// web objects
			$separator . $pathroot . "$directory_name/classes/objects" .				// web objects
			$separator . $pathroot . "$directory_name/classes/tools" .				// web objects
			

			$separator . $pathroot . "$directory_name/3rdparty/tcpdf" .					// pdf
			
			$separator . $pathroot . "$directory_name/classes/businessobjects" . // business objects
			$separator . $pathroot . "$directory_name/classes/html" . 			// dynamic HTML objects
			$separator . $pathroot . "$directory_name/classes/xml" . 			// xml 
			$separator . $pathroot . "$directory_name/classes/messaging" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/management" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/search" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/graphics" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/twitter" . 			// messaging service 
			
			$separator . $pathroot . "tcpdf".

			$separator . $pathroot . "classes";
			
			// handle modules
			$modules = null;
			if ( isset( $xml->modules ) )
				$modules = $xml->modules->children();
			if ( $modules )
				foreach ( $xml->modules->children() as $module )
					$paths .= $separator . $pathroot . $module . "/classes";
			
			return $paths;
	}
	
	function __autoload($classname) {
	
		global $separator;
		
	//	echo "paths = " . paths();
			
		$path = explode( $separator, paths() ); //get all the possible paths to the file (preloaded with the file structure of the project)
    
		foreach($path as $tryThis) {
			//try each possible iteration of the file name and use the first one that comes up
			// name.class.php first
			$candidate = $tryThis . '/' . $classname . '.php';
			//echo "_autoload(): I am looking to see if $candidate is a file that exists. <p>";
			$exists = file_exists( $candidate );
			if ($exists) {
				require_once($candidate);
				return true;
			} 		
		}
		
		//echo "_autoload(): I am unable to find any valid location for $classname<p>";
		return false;
	}
	
	//! get the basename of a file
	function xo_basename( $uri = __FILE__ ) { return basename($uri); }
	

	
	// finally...
	try {
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"bootstrap","main");
		$container = x_objects::instance();
		//$container->log( xevent::success, "$tag->event_format : x_objects bootstrap successfully loaded");
	} catch ( Exception $e ) {
		echo $tag->exception_format . " " . $e->getMessage();
		
	}
?>
