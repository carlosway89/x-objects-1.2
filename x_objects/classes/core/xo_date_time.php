<?php
/*
 * xo_date_time(): special class to instantiate an object with date/time properties
 */
class xo_date_time extends magic_object{
	// declare the constants
	const dt_format_1 = "/(([a-zA-Z]+)\s+([0-9a-zA-Z]+))\s+at\s+([0-9\:]+)([aApPmM]+)/";
	const dt_format_2 = "/(tomorrow)\s+at\s+([0-9\:]+)([aApPmM]+)/";
	const dt_format_3 = "(\s*(([0-9]+)\/([0-9]+)\/([0-9]+))\s+(at|At|AT|\@)\s+(([0-9]+)(\:[0-9]+)?(am|Am|AM|pm|PM|PM|a|A|p|P)))";
	/*
	 * construct given an optional date/time natural language string
	 */
	public function __construct( $nl ) {
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		$container->log( xevent::debug, "$tag->event_format : constructing new object with natural language $nl");
		
		// try to parse it
		if ( preg_match( self::dt_format_1,$nl,$hits)) {
			print_r( $hits);
			// first set month
			$this->month = $this->get_month( $hits[2]);
			// next the year
			$this->year = $this->get_year();
			// the day of the month
			$this->month_day = $this->get_month_day( $hits[3]);
			/* i dont believe we need these...
			$this->hours = $this->get_hour( $hits[4]);
			$this->minutes = $this->get_minutes($hits[4]);
			$this->pmity = $this->get_pmity( $hits[5]);
			*/
			// now the timestamp
			$this->timestamp = strtotime( "$this->month/$this->month_day/$this->year ". $hits[4].$hits[5]);
			// finally the display date time
			$this->display_date_time = date( 'm/d/Y h:iA',$this->timestamp);
		} elseif ( preg_match( self::dt_format_2,$nl,$hits)){
			echo "$tag->event_format: hits below<br>\r\n";
			print_r( $hits);	
		} elseif ( preg_match( self::dt_format_3,$nl,$hits)){
			echo "matched format 3!";
		}
		
	}
	
	//! magic get
	public function __get( $what ){
		switch( $what ){
			case 'slash_date':
				return date('n/j/Y',$this->timestamp);
			break;
			case 'twelve_hour_time':
				return date( 'h:iA',$this->timestamp);
			break;
			case 'display_date_time':
				//return date( );
			break;
			default:
				return parent::__get( $what);
			break;
		}
	}
	
	//! get month
	private function get_month( $str){
		global $container;
		$month_id = $container->services->utilities->month_id( strtolower( $str));
		if ( ! $month_id)
			$this->error = "Unrecognized month: " . $str;
		else
			$this->month = $month_id;
		return $month_id;
	}
	
	//! get the year
	private function get_year(){
		$year = date('Y');
		$month = date('m');
		if ( $month > $this->month)
			$year++;
		return $year;
	}
	
	//! get the month day as a number
	private function get_month_day( $str){
		if ( preg_match( '/([0-9]+)/',$str,$hits))
			return (int)$hits[1];	
	}
	
}
?>
