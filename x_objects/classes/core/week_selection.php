<?php

class week_selection extends magic_object implements x_class {

	public $dates = array();

	public function __construct( $start, $days, $format, $num_days = 7 ) {
	
		$this->start = $start;
		$this->format = $format;
		
		// get the day for the start date
		$day = strtolower( date( 'D', strtotime( $start )) );
		
		$date = $this->start;
		
		// go through seven days 
		for ( $i = 0; $i < $num_days ; $i++ ) {
		
			// if current day is in selection
			if ( in_array( $day , $days) ) {
				$index = count( $this->dates);
				$this->dates[ $index ] = $date;
			}
			
			// update date and day
			$date = date( $format , strtotime( $date . "+1 day"));
			$day = strtolower( date( 'D', strtotime( $date )) );
			
		}
	
	}
	
	//! check if it has a specific date
	public function has_date( $date ) {
	
		return in_array( $date, $this->dates );
	
	}

}

?>