<?php

class x_time extends magic_object implements x_class {

	private $debug = false;

	//! construct one
	public function __construct( $raw_time ) {
	
		if ( Debugger::enabled())
			$this->debug = true;
	
		$this->raw_time = $raw_time;
		
		if ( $this->debug )
			echo get_class() . ": raw time $raw_time<br>";
		
		$firstPart = floor( $raw_time  / 60 ) ;
		if ( $firstPart > 12 ) $firstPart -= 12;
		$amPm =  ($raw_time < 720) ? 'AM' : 'PM';
				
		$this->time = $firstPart . ':' . sprintf( "%02d" , $raw_time % 60) . $amPm;	
		
	}

	//! create a new one
	public static function create( $raw_time ) {
		return new x_time( $raw_time );
	}

}

?>