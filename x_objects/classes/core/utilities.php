<?php 
/*! 
 * Utility: A final class with various useful methods for on-screen display, and other calculations.
 * Purpose: primarily used to save time and increase efficiency for common tasks and repetitive steps
 */
class utilities implements x_service {
	private static $instance = null;
	private function __construct(){}
	//! returns a reference to the singleton instance of the class
    public static function instance() {
		// if the instance hasn't been created yet
        if (!isset(self::$instance)) {
			// use the current classname
            $C = __CLASS__;
			// and create the instance as a new object of that class
            self::$instance = new $C;
        }

		// return a reference to the instance
        return self::$instance;
    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	
	
	//! one week in seconds
	const week = 604800;
	
	//! translate a monthid into a name
	public static function getMonthName( $monthid ) {
	
		$names = array (
			"January",
			"February",
			"March",
			"April",
			"May",
			"June",
			"July",
			"August",
			"September",
			"October",
			"November",
			"December");
			
		return $names[ $monthid ];
	
	}
	
	//! is it a valid url?
	public static function is_url( $candidate ) {
	
		return preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", strtolower( $candidate) ); 

	}
	
	//! returns prior monday in a nice string format
	public static function prior_monday( $week_offset = 0 ) {
	
		$day = time();
		while ( date( 'l' , $day ) != 'Monday' )
			$day -= 86400;
		
		// apply week offset
		$day += self::week * $week_offset;
			
		return date( 'Y-m-d' , $day);
		
	}
	
	//! returns upcoming sunday in a nice string format
	public static function coming_sunday( $week_offset = 0) {
		
		$day = time();
		
		while ( date('l', $day ) != 'Sunday' )
			$day += 86400;
			
		// apply week offset
		$day += self::week * $week_offset;
			
		return date( 'Y-m-d' , $day);
	}
	
	//! returns the PHP version without the final revision
	public static function phpversion() {
		return substr( phpversion() , 0, 3);
	}
	
	//! does the given string begin with a vowell?
	public static function beginsWithVowell( $string ) {
		return preg_match ( '/^[aeiou]+(.)*/' , $string );
	}
	
	//! returns current timestamp
	public static function now() {
		
		$now = time(); 
	//	- ( 60 * 60 * 6);
//		if ( self::$debug ) Debugger::echoMessage( 'Utility::now(): is  ' . date( 'Y j F G:i:s', $now) );

		return $now;
	}
	
	//! is the given value a currency?
	public static function isCurrency( $token ) {
		return preg_match ( '/^[$]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/', $token );
	}
	
	//! is the given phone number valid?
	public static function isValidPhone( $token ) {
		$regex = '/\(?[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}/';
		return preg_match( $regex , $token );
	}
	
	//! is the specified date in the future?
	public static function isFuture( $datetime ) {
		return $datetime > time();
	}
	
	
	//! get the upcoming date stamp for the day of the week
	public static function getUpcomingDateFor( $day ) {
	
		$counter = 0;
		
		$aDay = 60 * 60 * 24;
		
		if ( self::$debug )
			Debugger::echoMessage( 'Utility::getUpcomingDateFor(): checking for the coming ' . $day );
			
		// get current date
		$date = Utility::now();
			
		if ( self::$debug )
			Debugger::echoMessage( 'Utility::getUpcomingDateFor(): today is ' . date( 'Y l j F G:i:s',$date));
			
		// if today, return it
		if ( $day === strtolower( date('l',$date) ) ) {
		if ( self::$debug )
			Debugger::echoMessage( 'Utility::getUpcomingDateFor(): returning ' . date( 'Y j F',$date));
			return strtotime(date( 'Y j F',$date));
		}
			
		// if matched, return it, otherwise add one day
		while ( $day != strtolower( date('l',$date)) && $counter++ < 10) {
			if ( self::$debug )
				Debugger::echoMessage( 'Utility::getUpcomingDateFor(): checking  ' . date( 'Y l j F G:i:s',$date));
			$date = $date + $aDay;
		}
		if ( self::$debug )
			Debugger::echoMessage( 'Utility::getUpcomingDateFor(): returning ' . date( 'Y j F',$date));
		
		return strtotime(date( 'Y-m-d',$date));
	
	}
	
	/* redirect the page to a new location
	public static function redirectTo( $location, $returnURL = null) {
				
		$returl = $returnURL ?	"?returl=$returnURL" : '';
		
		 header( 'Location: ' . $location . $returl ) ;
	}
	*/
	//! redirect the page to a new location
    public static function redirectTo( $location, $returnURL = null) {
         
        $token = preg_match( '/\?/',$location) ? '&' : '?';
         
        $returl = $returnURL ? 'returnurl=' . $returnURL : '';
         
         header( 'Location: ' . $location . $token . $returl ) ;
    }

	
	/**
 * Function to calculate date or time difference.
 * 
 * Function to calculate date or time difference. Returns an array or
 * false on error.
 *
 * @author       J de Silva                             <giddomains@gmail.com>
 * @copyright    Copyright &copy; 2005, J de Silva
 * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
 * @param        string                                 $start
 * @param        string                                 $end
 * @return       array
 */
 
 //! get the difference in time between two string dates
public static function get_time_difference( $start, $end )
{
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}
	
	//! returns true if the $entity is an object of type $class or an array of objects of the same class, otherwise returns false
	public static function isObjOrArray( $entity, $class) {
		
		if ( ! is_array( $entity ) && ! is_object( $entity ) )
			return false;
		
		if ( is_object( $entity ) && ! get_class( $entity ) === $class )
			return false;
			
		if ( is_array( $entity ) && ( ! is_object( $entity[0] ) || ( is_object( $entity[0] && ! get_class( $entity[0]) === $class ) ) ) )
				return false;
				
		return true;
	}
	/*! 
	 * getPageName(): get the name of the current page (without the full URL)
	 * @returns: (string) base name of invoking page
	 */
	public static function getPageName() {
 		return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	}


	/*
	 * isNewFormSubmission(): checks if the previous form submission is new
	 */
	public function isNewFormSubmission() {
		
		// is a previous session set for this form and is the form being posted
    	if (isset($_SESSION["myform_key"]) && isset($_REQUEST["myform_key"])) { 
    			
      		// is the form posted and do the keys match
      		if( $_REQUEST["myform_key"] == $_SESSION["myform_key"] ){
        		return false;
      		} 
    	}
		
		return true;
		
	}

	/*
	 * echobrnl(): utility to echo a string, and append <br> and \r\n
	 * String: (string) the output to display
	 */
	public function echobrnl ( $String ) {
		echo $String . '<br>' . "\r\n";
	}
	
	public function random_password($length=10){ return self::createRandomPassword($length); }
	
	/*
	 * createRandomPassword(): a simple utility to create a random password of a specified length
	 * $Length: specifies how long the password should be
	 * returns: a string containing the password
	 */
	public static function createRandomPassword( $Length = 10 ) {

		// the character set to create the password
    	$chars = "abcdefghijkmnopqrstuvwxyz023456789";
    	
    	// initialize a randomized number
    	srand((double)microtime()*1000000);

    	$i = 0;
		$pass = '' ;

		// ( you can tell a C programmer wrote this code :-)
		while ($i < $Length ) {

        	$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
        	$pass = $pass . $tmp;
			$i++;
    	}
    	return $pass;

	}
	
	public static function sendEmail( $to, $subject, $message, $headers = null, $parms = null ) {
		// headers when sending email with PHP
		return mail($to, $subject , $message, $headers, $parms);
		
	}
	
	//!isValidEmail(): Checks whether argument is a valid email, and if so returns true otherwise false
	public static function isValidEmail($email) {
		return preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/", $email);	
	}


	/*
	 * reCase(): returns string reformatted with mixed case, upper for first
	 * character of each token, the rest lower
	 */
	 public function reCase($string) {
		return preg_replace('/\b(\d*)([a-z])/e', '"$1".ucfirst("$2")', strtolower($string));
	}
	
	/*
	 * reURL( $URL ): reformulate a URL based on the standard form
	 * e.g. www.foo.bar becomes http://www.foo.bar
	 */
	public function reURL( $URL ) {
	
		if ( strpos( $URL , 'http://') === false )
			return 'http://' . $URL;
		else return $URL;
		
		
	}
	
	
	//! is the current browser Internet Explorer 7 compatible?
	public static function isRunningIE7Compatible() {
	
		return ! strcmp ( 'IE7' , substr ( self::getBrowser() , 0 , 3 ) ) ;
	}
	
	public function browser(){
		$useragent= isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'command-line';	
		if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
    		$browser_version=$matched[1];
    		$browser = 'ie' . substr( $browser_version, 0 ,1);
		} elseif (preg_match( '|Opera ([0-9].[0-9]{1,2})|' ,$useragent,$matched)) {
    		$browser_version=$matched[1];
    		$browser = 'Opera';
		} elseif(preg_match('|Firefox/([0-9\.]+)|' ,$useragent,$matched)) {
        	$browser_version=$matched[1];
        	$browser = 'ff' . substr ( $browser_version, 0 , 1) ;
        } elseif(preg_match('|Chrome/([0-9\.]+)|' ,$useragent,$matched)) {
            $browser_version=$matched[1];
            $browser = 'Chrome' . substr ( $browser_version, 0 , 2) ;
        } elseif(preg_match('|Safari/([0-9\.]+)|' ,$useragent,$matched)) {
        	$browser_version=$matched[1];
        	$browser = 'Safari' ;
		}
		return isset($browser)?$browser:null;
		
	}

		
	public function ms_display_of( $float) {
		$sec = floor( $float);
		$ms = (float)number_format(($float - $sec)*1000,2);
		return "$sec s $ms ms";
	}
	
	// is given string a proper time?
	public function is_time( $str) {
		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		//$container->log( xevent::debug, "$tag->event_format : checking if $str is a valid time...");
	
		// first explode by colon
		$parts = explode(':',$str);
		// if not two parts, it isn't
		if ( ! count($parts) == 2 ) { 
			$container->log( xevent::debug , "$tag->event_format: $str is not a valid time, because it isnt colon-splittable (urgh)");
			return false;
		} else {
			$hour = (int)$parts[0];
			// if not within hour range
			if ( $hour < 0 || $hour > 23 ) { 
				$container->log( xevent::debug , "$tag->event_format: $str is not a valid time, because the hour is out of range");
				return false;
			} else {
				// try to split by AM PM
				if ( preg_match( '/([0-9]+)(AM|am|Am|Pm|PM|pm)/',$parts[1],$matches)) {
					$mins = (int)$matches[1];
					$pmity = $matches[2];	
				} else {
					$mins = (int)$parts[1];
				}
				// if minutes out of range
				if ( $mins < 0 || $mins > 59) { 
					$container->log( xevent::debug , "$tag->event_format: $str is not a valid time, because the minutes are out of range");
					return false;
				// if pmity and 24 hour it isnt
				}
				if ( @$pmity != '' && ($hour > 12 )) { 
					$container->log( xevent::debug , "$tag->event_format: $str is not a valid time, because can't mix PMity $pmity and 24 hour hour $hour");
					return false;
				}
				return true;
			}	
		}
	}
	
	//! returns a timestamp that is $hours hours from now
	public function hours_from_now( $hours) {
		return strtotime(date("Y-m-d g:iA") . " +$hours hours");
	}
	
	//! get the month id for a month name
	public function month_id( $name){ return date( 'n', strtotime($name)); }
	
	//! is a string parenthesis balanced?
	public function paren_balanced( $str ){
		return ( substr_count($str,"(") == substr_count($str, ")"))? true : false;
	}

}
?>
