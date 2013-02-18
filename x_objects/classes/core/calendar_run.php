<?php

// representation of a calendar week
class calendar_run extends magic_object {

	//! debugging
	private $debug = false;

	//! construct with a day of the month
	public function __construct( $day, $month = null, $year = null, $run = 7 ) {
	
		if ( Debugger::enabled() )
			$this->debug = true;
	
		// by default use a week
		$this->run = $run;
		// set the day based on user request
		$this->first_day = $day;
		
		
		// month is implied as this month if not specified
		$this->month = ($month) ? $month : date('F');
		
		// same for the year
		$this->year = ($year) ? $year : date('Y');
	
		$this->monthid = date('n',strtotime( "$this->year $this->first_day $this->month")); 
		
		if ( $this->debug ) echo get_class() . ": contructed a new run for $this->month $this->year." .
			" the first day of the run is $this->first_day<br>";
		
		
		// create an array of weekdays
		$wdays = array ( 'Sun','Mon','Tue','Wed','Thu','Fri','Sat');
		
		// get name of first day in week
		$dayname = date( 'D' , strtotime( "$this->first_day $this->month $this->year"));
		
			//echo "first dayname is $dayname for $this->month $this->first_day $this->year<br>";
		
		// get the position of the day name in weekdays
		$daypos = array_search ( $dayname, $wdays);
		
		// get current date
		$cur_day = $this->first_day;
		
		// do for seven days
		for ( $i = 1 ; $i<= $this->run ; $i++ ) {
		
			// set member names
			$member = "dayname$i";
			$member2 = "day$i";
			
			// set day name as current one
			$this->$member = $wdays[ $daypos ];
			
			// date too
			$this->$member2 = $cur_day;
			
			// increment pointers
			$daypos++; $cur_day++;
			
			// wrap around in names array
			if ( $daypos > 6) $daypos = 0;
		
		}
		
	
	}
	
	//! is a given day position the current day?
	public function is_today( $position ) {
	
		$member = "day$position";
		
		return ($this->$member == date('j')) ? 'today' : '';
	
	}
	
	//! magic get
	public function __get( $what ) {
	
		switch ( $what ) {
		
			// number of days
			case 'num_days':
			
				return $this->run;
				
			break;
		
			default:
			
				return parent::__get( $what );
				
			break;
		
		}
	}

}

?>