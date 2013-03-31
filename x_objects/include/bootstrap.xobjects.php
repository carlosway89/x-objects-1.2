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
    // x-objects classes directories
    private $xclass_dirs = array('apis','behaviors','businessobjects','core','data','graphics','html',
        'lightweight','media','messaging','objects','search','services','sql','twitter',
        'vendors','webobjects','xml');
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
        $paths = $pathroot . "/classes";
        foreach ($this->xclass_dirs as $dir)
            $paths .= $separator.$pathroot."/classes/$dir";
        $paths .= $separator . $pathroot . "classes".
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
