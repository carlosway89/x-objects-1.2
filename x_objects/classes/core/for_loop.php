<?php
/*
 * class representation of a <for-loop> xElement contruct
 */
class for_loop extends magic_object {
	
	// constructor
	public function __construct($start,$end,$incr,$view,$value){
		$this->start = $start;
		$this->end = $end;
		$this->incr = $incr;
		$this->view = $view;
		$this->value = $value;
	}
	
	// magic get
	public function __get( $what ){
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

		switch( $what ){
			case 'xhtml':
				$html = "";
                if  ($container->debug) echo "$tag->event_format: getting xhtml start is $this->start end is $this->end incr is $this->incr<br>";
                /*
                     * yep, you guessed it.  this is where the for loop actually happens,
                     * and the magic occurs :-)
                     */
				 for ( $i = $this->start; $i <= $this->end; $i += $this->incr) {
                     if  ($container->debug) echo "$tag->event_format: for loop i=  $i<br>";
				 	// set counter so it can be accessed using xquery e.g. [get:counter]
				 	$this->counter = $i;
				 	// set a value for the same reason
				 	$this->evaluated = xQuery::parse( $this->parse_vars( $this->value ));
				 	// first get the view as an xml string
				 	$view_name = $this->view;
				 	/*global $$view_name;
				 	if ( ! isset( $$view_name))
				 		throw new ObjectNotInitializedException( "$tag->exception_format : the view <span style='color: green'>$view_name</span> cannot be found.  Be sure it is defined as an XML string in your view file");
				 	$view = $$view_name; */
				 	// now render the current item as an x-object, using the specified view
				 	$html .= x_object::create( $this->view)->html( $this);
				 }
				 return $html;
			break;
			default:
				return parent::__get( $what);
			break;
		}
		
	}
	
	// create a new for loop
	public static function create($start,$end,$incr,$view,$value){ return new for_loop($start,$end,$incr,$view,$value); }
	
	// parse vars to substitute local loop values in an eval xquery
	private function parse_vars($str){
		if ( preg_match('/\{([a-zA-Z0-9]+)\}/',$str,$hits)) {
			$member = $hits[1];
            //echo $member . " ". $this->$member;
			return preg_replace("/\{$member\}/",$this->$member,$str);
		}
	}
	
}

?>
