<?php

// a calendar matrix has an entry where each date falls based on the month and year
class calendar_matrix extends magic_object {

	private $debug = false;

	// construct given a month
	public function __construct( $month, $year = null ) {
	
		if ( Debugger::enabled() )
			$this->debug = true;
	
		$this->month = $month;
		$this->monthid = date ('n', strtotime( "1 $this->month $this->year"));
		$this->year = ($year) ? $year : date( 'Y');
		
		// set all the days
		//echo "$this->year-$month-" . '01';
		$firstday = date( 'D' , strtotime( "1 $this->month $this->year"));
		
		if ( $this->debug )
			echo get_class() . ": contructed a new one for $this->month $this->year<br>";
		
		// create an array of weekdays
		$wdays = array ( 'Sun','Mon','Tue','Wed','Thu','Fri','Sat');
		
		// are we inside the month yet?
		$inside = false;
		$curday = 1;
		$lastday = date( 't', strtotime( "1 $this->month $this->year"));
		
		// go through all the 42 slots
		for ( $i = 1 ; $i<=42 ; $i++ ) {
		
			// set index for weekdays
			$windex = $i % 7 -1;
			if ( $windex == -1 ) $windex = 6;
			
			// set member
			$member = "day$i";

			// if not inside yet
			if ( ! $inside ) {
			
				if ( $wdays[$windex] == $firstday ) {
					$inside = true;
					$this->$member = $curday;
					// advance current day
					$curday++;

				} else $this->$member = '&nbsp;';
			} else {
			
				
				$this->$member = ($curday <= $lastday) ? $curday : '&nbsp;';
				$curday++;
			
			}

		
		}
		
	}
	
	// magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			// get total days for the month
			case 'total_days':
			
				return  date( 't', strtotime( "1 $this->month $this->year"));
		
			break;
					
			// is this the current month
			case 'is_this_month':
			
				return ( $this->year == date('Y') && $this->month == date('F')) ?
					"this-month" : null;
					
			break;
		
			// get number of remaining days in month
			case 'remaining_days':
			
				// if we are in current month...
				if ( $this->month == date('F'))
					return  date( 't', strtotime( "1 $this->month $this->year")) - date('j') + 1;
			
			break;
		
			case 'sixth_row_class':
			
				return ( $this->day36 != '&nbsp;') ? '' : 'hidden';
				
			break;
			
			// get the entire run
			case 'entire_run':
			
				return new calendar_run( 1,$this->month,$this->year, $this->total_days);
				
			break;
			
			// get the run from the current day
			case 'current_run':
			
				$first_day = ( date('F') == $this->month ) ? date('j') : 1;
				
				return new calendar_run( $first_day, $this->month, $this->year, $this->remaining_days);
				
			break;
		
			case 'current_week':
			
				return new calendar_week( date( 'j' ) );
				
			break;
		
			default:
			
				return parent::__get( $what );
				
			break;
		
		}
		
	}
	
	
	// is a day today and thus should be selected?
	public function is_today( $day ) {
	
		// if we're not in current month, return
		if ( $this->month != date('F'))
			return null;
	
		$member = "day$day";
		return ( $this->$member == date('j')) ? 'selected-day' : null;
	
	}
	
	//! is a day in the past?
	public function is_past( $day ) {
	
		// if the month is in the future return null
		if ( $this->monthid != date( 'n'))
			return null;
	
		$member = "day$day";
		return ( $this->$member < date('j')) ? 'past' : null;
	
	}
	
	//! first day class
	public function first_day_class( $day ) {
	
		$member = "day$day";
		return ( $this->$member == 1 ) ? "firstday" : '';
	}
	
	//! get the month diff class to disable a control
	public function month_diff_class( $diff ) {
	
		return ( abs( date( 'n', strtotime( "1 $this->month $this->year")) - date('n')) == $diff ) ?
			"months$diff" : '';

	
	}

}