<?php 

// grab x_objects location
global $xobjects_location,$container;

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
$directory_name = "";

$autoload_bypass_exception = false;
	// default separator
$platform = preg_match( '/;/' , ini_get( "include_path" ) ) ? "win" : "ux";
$separator = $platform == 'win' ? ';' : ':';

//echo "sep = $separator<br>\r\n";
	// include configuration and database connection files
	$pathroot = $xobjects_location;
	$webroot = "/";
	define ( 'PATHROOT' , $pathroot );
	
	//echo $pathroot;
	
	// function to obtain all possible paths to find classfiles
	function paths() {
        $platform = preg_match( '/;/' , ini_get( "include_path" ) ) ? "win" : "ux";
        $separator = $platform == 'win' ? ';' : ':';

		global $xobjects_location, $directory_name, $webapp_location;
        $pathroot = $xobjects_location;
		//echo "$pathroot $directory_name $separator<br>";
		
		$paths = $pathroot . "$directory_name/classes" .					// platform classes
			$separator . $pathroot . "$directory_name/classes/apis" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/behaviors" .					// data
			$separator . $pathroot . "$directory_name/classes/data" .					// data
			$separator . $pathroot . "$directory_name/classes/core" .					// core app services
			$separator . $pathroot . "$directory_name/classes/sql" .					// core app services
			$separator . $pathroot . "$directory_name/classes/services" .				// service classes
            $separator . $pathroot . "$directory_name/classes/graphics" .				// service classes
            $separator . $pathroot . "$directory_name/classes/webobjects" .				// web objects
			$separator . $pathroot . "$directory_name/classes/objects" .				// web objects
			$separator . $pathroot . "$directory_name/classes/tools" .				// web objects
            $separator . $pathroot . "$directory_name/classes/vendors" .				// vendors (third party)
            $separator . $pathroot . "$directory_name/classes/businessobjects" . // business objects
			$separator . $pathroot . "$directory_name/classes/xml" . 			// xml 
			$separator . $pathroot . "$directory_name/classes/messaging" . 			// messaging service 
			$separator . $pathroot . "$directory_name/classes/management" . 			// messaging service 
            $separator . $pathroot . "$directory_name/classes/vendors/stripe/lib" . 			// messaging service
            $separator . $pathroot . "$directory_name/classes/vendors/stripe/lib/Stripe" . 			// messaging service
            $separator . $pathroot . "classes".
			$separator . "$webapp_location/app/models" .
			$separator . "$webapp_location/app/classes" 
			 ;
			
			// handle modules
			$modules = null;
			if ( isset( $xml ) && $xml->modules )
				$modules = $xml->modules->children();
			if ( $modules )
				foreach ( $xml->modules->children() as $module )
					$paths .= $separator . $pathroot . $module . "/classes";
			
			return $paths;
	}
	
	function __autoload($classname) {
        global $autoload_bypass_exception;
        if (strpos($classname, 'CI_') === 0)
        {
            return;
        }
	    //echo "this is my autoloader";

        $platform = preg_match( '/;/' , ini_get( "include_path" ) ) ? "win" : "ux";
        $separator = $platform == 'win' ? ';' : ':';

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
		if ( ! $autoload_bypass_exception ) throw new Exception("<span style='color:blue;font-weight: bold;font-family:courier,sans-serif'>$classname</span>: Not a valid Class Name, or unable to find in search path");
        else return false;
	}
	
	//! get the basename of a file
	function xo_basename( $uri = __FILE__ ) { return basename($uri); }

    // extract page vars
    function xo_extract_pagevars(){
        global $page_vars;
        if ( is_array($page_vars))
            foreach( $page_vars as $index=>$value)
                $$index = $value;
    }

?>
