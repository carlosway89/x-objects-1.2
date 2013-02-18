<?php

class x_array extends AttributedObject {

	private $debug = false;
	
	private static $date = null;

	// construct
	public function __construct( $start, $end, $incr, $view, $type = null, $time_format = 12, $unique_id = 1 ) {
	
		if ( Debugger::enabled() )
			$this->debug = true;
			
		$this->start = $start;
		$this->end = $end;
		$this->incr = $incr;
		$this->view = $view;
		$this->type = $type;
		$this->time_format = $time_format;
		$this->unique_id = $unique_id;
		
		if ( $this->debug )
			echo get_class() .  "start end incr view $start $end $incr $view $time_format<br>";
		
	}
	
	//! magic set
	public function __set( $what, $how ) { $this->set( $what, $how ); }
	//! magic get
	public function __get( $what ) { return $this->get( $what ); }
	
	//! return as xhtml
	public function xhtml() {
	
		$html = '';
		
		switch( $this->type ) {
		
			case 'date':
			
				$start = strtotime( $this->start );
				$end = strtotime( $this->end );
				if ( $this->incr == 'day' )
					$incr = 60*60*24;
										
				if ( $this->debug ) {
				
					echo get_class() .  " start end incr $start $end $incr <br>";
					return false;
				}
					
			break;
		
			default:
				$start = $this->start;
				$end = $this->end;
				$incr = $this->incr;
				
			break;
		}
		
		for ( $counter = $start ; $counter <= $end ; $counter += $incr ) {
		
			if ( $this->type == 'date' ) {
				self::$date = $this->date = date( 'm/d/Y' , $counter );
				$this->raw_date = $counter;
				$this->date_header = date( 'l F j, Y', $counter );
			}
			else {
				$this->date = self::$date;
				$this->raw_date = strtotime( $this->date );
				}
			// get the current actual counter
			$this->current = $counter;
			// get the "normalized" counter
			$this->normalized_counter = $counter - $start + 1;
			
			$hour = floor( $counter / 60 );
			$amPm = ($this->time_format == 12 ) ? ($hour > 11 ? 'pm' : 'am' ) : '';
			
			if ( $this->time_format == 12 && $hour > 12 )
				$hour = $hour -12;
				
			
			$this->time =  $hour . ":" . sprintf( "%02d" , $counter % 60) . " $amPm";
			
			$html .= x_object::create( $this->view )->xhtml( $this );
		}
		return $html;
	
	}

	// create a new one
	public static function create( $start, $end, $incr, $view, $type = null, $time_format = 12 , $unique_id = 1) {
		return new x_array( $start, $end, $incr, $view , $type, $time_format, $unique_id);
	}

}

?>