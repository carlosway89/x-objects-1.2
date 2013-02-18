<?php

//! business object 
class xevent extends business_object {
	// event logging level indicators
	const notice = 0;
	const success = 1;
	const failure = 2;
	const warning = 3;
	const exception = 4;
	const debug = 5;
	const error = 6;
	
	// twitter-specific codes
	const twitter_notice = 7;
	const twitter_success = 8;
	const twitter_failure = 9;
	const twitter_warning = 10;
	const twitter_exception = 11;
	const twitter_debug = 12;
	const twitter_error = 13;
	
	const app_event = 100;
	
	// logging types
	const log_file = 0;
	const log_db = 1;
	
	// static indicator for logging type
	public static $log_type = self::log_db;

	//! construct with a query
	public function __construct( $search = null ) {
		
	
		// construct parent
		parent::__construct( get_class() , $search );
	}

	//! get datasource in static context
	public static function source() {
	
		return new DataSource2( RealXML::create( 'bo-' . strtolower( get_class() ) )->xml()->datasource );

	}
	
	//! return as well-formed html
	public function html( $view  ) {
	
		// create as a web snippet and return as html
		return x_object::get( $view )->html( $this );
		
	}

	//! magic get
	public function __get( $what ) {
		switch( $what){
			case 'event_human_time':
				return new human_time(strtotime( $this->event_time));
			break;
			default:
					return parent::__get( $what );
			break;
		}
	
		
	}
	
	//! magic set
	public function __set( $what , $val) {
		return parent::__set( $what, $val);
	}
	
	// log a new event
	public static function  log($type,$message) {
		$css = array(
			"xevent-notice",
			"xevent-success",
			"xevent-failure",
			"xevent-warning",
			"xevent-exception", 
			"xevent-debug", 
			"xevent-error",
			"xevent-twitter xevent-twitter-notice",
			"xevent-twitter xevent-twitter-success",
			"xevent-twitter xevent-twitter-failure",
			"xevent-twitter xevent-twitter-warning",
			"xevent-twitter xevent-twitter-exception", 
			"xevent-twitter xevent-twitter-debug", 
			"xevent-twitter xevent-twitter-error",
			100 => "xevent-app "
			
			); 
		$tag = array(
			"[ NOTICE ]",
			"[ SUCCESS ]",
			"[ FAILURE ]",
			"[ WARNING ]",
			"[ EXCEPTION ]",
			"[ DEBUG ]",
			"[ ERROR ]",
			"<div class=\"twitter-tag\"></div>[ NOTICE ]",
			"<div class=\"twitter-tag\"></div>[ SUCCESS ]",
			"<div class=\"twitter-tag\"></div>[ FAILURE ]",
			"<div class=\"twitter-tag\"></div>[ WARNING ]",
			"<div class=\"twitter-tag\"></div>[ EXCEPTION ]",
			"<div class=\"twitter-tag\"></div>[ DEBUG ]",
			"<div class=\"twitter-tag\"></div>[ ERROR ]",
			100 =>"[ APP EVENT ]"
			
		);
		$event = new xevent();
		$event->event_type_id = $type;
		$event->message = $tag[$type]. " $message";
		$event->css_class = $css[$type] . " xo-round3 ";
		if ( self::$log_type == self::log_db )
			$event->save();
		else
			$event->write();
	}
	
	//! log a new event
	public static function logv($type,$file,$line,$class,$method,$msg) {
		return self::log($type,"[ $file ][ $line ][ $class{} ][ $method() ][ $msg ]"); 
	}
	
	//! empty the entire log
	public static function truncate(){
		// truncate the table
		if ( mysql_service::truncate( "xo_event")) 
			echo "success xevent truncate";
		else 
			echo "error xevent truncate: " . mysql_service::error();
	}
	

}

?>