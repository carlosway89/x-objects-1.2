<?php
/** a codetag is used to track a specific location in code for debugging, events and exceptions
 *
 * @property string $event_format the codetag in a format suitable for event logging
 * @property string $exception_format the codetag in a format suitable to throw an exception
 */
class xo_codetag extends magic_object {
    // save the last action timestamp;
    private static $last = 0;
	//! construct given all the usual suspects
	public function __construct(
		$filename,		// the name of file where it happened
		$line,			// the line where it happened
		$class,			// name of class
		$method		// name of method or function
	) {
        //if ( ! self::$last) self::$last = time();
		// save all
		$this->filename = xo_basename($filename);
		$this->line = $line;
		$this->class = $class;
		$this->method = $method;
		
	}
	//! magic get
	public function __get($what){
        global $container;
        self::$last = time();
		switch( $what ) {
			case 'app_event_format':
				$date = date('Y-m-d H:i:s');
				return "";
			break;
	
			case 'mem_usage': return number_format(((memory_get_usage()/1024)/1024),2)."MB "; break;
			case 'debug_format':
			case 'event_format':
                $now = time();
				$date = date('Y-m-d H:i:s.u',microtime(true));
                $tag = "[ $date ] [ $this->filename ][ $this->line ][ $this->class{} ][ $this->method" . "() ] [ $this->mem_usage ] ";
                $diff = $now - self::$last;
                if ( $container->performance_tracking )
                    $tag ="<br>\r\n $diff seconds since last action $now - ".self::$last."<br>\r\n$tag";
                self::$last = $now;
                return $tag;
			break;
			case 'exception_format':
				return "$this->filename [ $this->line ] $this->class{}::$this->method"."(): An Application Exception has been thrown: ";
			break;
			default:
				return parent::__get($what);
			break;
		}
	}
	
	/**
	 * create a new codetag.  this method is great for chaining commands.
	 *
	 * @param $filename the name of the file
	 * @param $line the line number
	 * @param $class the name of the class
	 * @param $method the name of the method
	 * @return object returns the new codetag
	 */
	 public static function create( $filename,$line,$class,$method){ return new xo_codetag($filename,$line,$class,$method); }
	
/**
	 * create a new codetag from a JSON string.  this method is best for calls from jquery/javascript.
	 *
	 * @param $json the json string
	 * @return object returns the new codetag
	 */
	public static function from_json( $jsonstr ){
		echo $jsonstr;
		$obj = json_decode( $jsonstr );
		return new xo_codetag($obj->filename,$obj->line,$obj->class,$obj->method);
	}
	 
}

?>