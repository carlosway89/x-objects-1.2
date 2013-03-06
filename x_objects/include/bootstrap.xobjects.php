<?php

/**
 * Bootstrap Class, encapsulated to make things a bit easier
 */
class xo_bootstrapper {
    private $debug = false;      // you can toggle debugging as needed
    private $xloc = '';         // default x-objects location
    private $wloc = '';         // webapp location
    private $bypass_exception = false; // whether to bypass exceptions
    private $platform = '';
    private $separator = '';
    private $php_self = '';
    // connection xml
    private $conn_xml = null;
    public function __construct(){
        global $autoload_bypass_exception,$xobjects_location,$webapp_location;
        $this->bypass_exception = $autoload_bypass_exception;
        $this->platform = preg_match( '/;/' , ini_get( "include_path" ) ) ? "win" : "ux";
        $this->separator = $this->platform == 'win' ? ';' : ':';
        if ( $this->debug) echo "xloc is $this->xloc\r\n";
        $this->wloc = $webapp_location;
        $this->php_self = $_SERVER['PHP_SELF'];
        $this->xloc = $xobjects_location;
        if ( ! $this->xloc){
            $this->xloc = $this->guessed_location();
        }
        if( $this->debug) echo "php self is $this->php_self\r\n";
        $this->conn_xml = simplexml_load_string(
            "<connection>
            <database>#dbname</database>
            <host>#dbhost</host>
            <username>#dbuser</username>
            <password>#dbpass</password>
            <socket>#dbsocket</socket>
            <port>#dbport</port>
        </connection>"
        );
    }
    public function go(){

    }
    // function to obtain all possible paths to find classfiles
	private function paths() {
        $platform = $this->platform;
        $separator = $this->separator;
        // hot fix for include path
        if (! $this->xloc ){
        }
        $pathroot = $this->xloc;
        $paths = $pathroot . "/classes" .					// platform classes
            $separator . $pathroot . "/classes/apis" . 			// messaging service
            $separator . $pathroot . "/classes/behaviors" .					// data
            $separator . $pathroot . "/classes/data" .					// data
            $separator . $pathroot . "/classes/core" .					// core app services
            $separator . $pathroot . "/classes/sql" .					// core app services
            $separator . $pathroot . "/classes/services" .				// service classes
            $separator . $pathroot . "/classes/graphics" .				// service classes
            $separator . $pathroot . "/classes/webobjects" .				// web objects
            $separator . $pathroot . "/classes/objects" .				// web objects
            $separator . $pathroot . "/classes/tools" .				// web objects
            $separator . $pathroot . "/classes/vendors" .				// vendors (third party)
            $separator . $pathroot . "/classes/businessobjects" . // business objects
            $separator . $pathroot . "/classes/xml" . 			// xml
            $separator . $pathroot . "/classes/messaging" . 			// messaging service
            $separator . $pathroot . "/classes/management" . 			// messaging service
            $separator . $pathroot . "/classes/vendors/stripe/lib" . 			// messaging service
            $separator . $pathroot . "/classes/vendors/stripe/lib/Stripe" . 			// messaging service
            $separator . $pathroot . "classes".
            $separator . "$this->wloc/app/models" .
            $separator . "$this->wloc/app/classes"
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

    public function autoload($classname){
        if ( $this->debug ){
            echo "bootstrap: paths are ".ini_get('include_path');
        }

        $path = explode( $this->separator, $this->paths() ); //get all the possible paths to the file (preloaded with the file structure of the project)

        foreach($path as $tryThis) {
            $candidate = $tryThis . '/' . $classname . '.php';
            if ( $this->debug ) echo "_autoload(): I am looking to see if $candidate is a file that exists. <p>\r\n";
            $exists = file_exists( $candidate );
            if ($exists) {
                require_once($candidate);
                return true;
            }
        }

        if ( $this->debug ) echo "_autoload(): I am unable to find any valid location for $classname<p>";
        global $autoload_bypass_exception;
        if ( ! $autoload_bypass_exception ) throw new Exception("<span style='color:blue;font-weight: bold;font-family:courier,sans-serif'>$classname</span>: Not a valid Class Name, or unable to find in search path");
        else return false;

    }
    private function guessed_location(){
        $loc = '';
        if ( $this->php_self){
            $slices = explode('/',$this->php_self);
            $loc = implode('/',array_slice($slices,0,count($slices)-1));
        }
        return $loc;
    }
}

$boot = new xo_bootstrapper();


function __autoload($classname) {
    global $boot;
    return $boot->autoload($classname);
}

//! get the basename of a file
function xo_basename( $uri = __FILE__ ) { return basename($uri); }

function guessed_location(){
    $slices = explode('/',$_SERVER['PHP_SELF']);
    $loc = implode('/',array_slice($slices,0,count($slices)-1));
    return $loc;
}


?>
