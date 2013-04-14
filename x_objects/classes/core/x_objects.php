<?php
@session_start();
/**
 * This core class is a Singleton that represents the X-Objects instance, or application container
 * @property bool $performance_tracking indicates if performance tracking is enabled
 * @property object $services the services manager instance

 */
class x_objects {
    // wrappers for performance stats
    const pws = '<span style="color: lightgray; background-color: #006400;z-index: 10000; float: left;">';
    const pwe = "</span><br>\r\n";
	//! the instance
	private static $instance = null;
	//! configuration for the container
	private $config = null;
	//! debug file handle
	private $debug_file = false;
    // last error (from any kind)
    public $last_error = null;
    // object to track performance
    private $performance = null;
	//! private constructor -- singleton
    // is ajax running?
    public $is_ajax = false;
    private $platform = null;
    private $debug_manager = null;      // manages debugging
	private function __construct() { 
		global $webapp_location;
		// set up logging and debugging
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// load configuration
		try {
			$this->config = new RealXML ( 'x-objects' );
		} catch ( Exception $e ) {
			throw new ObjectNotInitializedException( 'X-Objects is unable to find its configuration file <strong>x-objects.xml</strong>.  Please ensure the file is in /xml or /x_objects/xml and try again');
		}
        // set up debug manager
        $this->debug_manager = new xo_debug_manager( (object)$this->config->xml()->debugger);

        if ( $this->debug && $this->debug_level >= 2){
            echo "$tag->event_format: xobjects configuration is ". $this->config->xml()->asXML() . "<br>";
        }

        // enable peformance tracking
        if ( $this->performance_tracking ){
            $this->performance = new xo_performance_tracker();
            echo self::pws . "$tag->event_format: performance tracking is enabled". self::pwe;
        }

        // set timezone
		$tz = (string)$this->config->xml()->timezone;
		if ( $tz ) { 
			date_default_timezone_set( $tz );
		} else $tz = @date_default_timezone_get();
		$this->timezone = $tz;
		
		// set css compatibility
		if ( (bool) $this->config->xml()->css_compatibility )
			RealXML::$css_compatible = $this->browser();
		
        if ( $this->debug && $this->debug_level >= 2) echo "$tag->event_format: done constructing container<br>";
        $this->platform = preg_match( '/;/' , ini_get( "include_path" ) ) ? "win" : "ux";
    }
	
	public function destroy(){
		$t = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        // display performance status
        if ( $this->performance_tracking){
            echo self::pws."$t->event_format: performance stats are $this->performance" .self::pwe;
        }

        // explicity destroy any service singleton
        $sc = (string)$this->xml->app->name;
        if ( class_exists($sc) ){
            $sc::instance()->destroy();
        }

		if ( $this->debug){
			//echo "DEBUG!";
			if ( $this->debug_file ) {
				fputs( $this->debug_file, "$t->event_format : x_objects shutting down\r\n\r\n\r\n");
				fclose( $this->debug_file );
			} else {
				echo "$t->event_format : x_objects shutting down<br>\r\n";
			}
		}
		// only do every 5th time
		static $nth = 1;
		srand();
		if ( rand(0,4)==3) { 
			//echo "yep";
			global $x_start_time,$key,$x_start_mem;
			$key = ($key)?$key:"unknown";
			if ( ! @$x_start_time===false) { 
				$time = microtime(true)-$x_start_time;
				$query = "insert into `xo_statistic` (`key`,`type`,`value`) values('$key','page_load',$time)";
				//mysql_service::query( $query, __FUNCTION__);
				//echo mysql_service::error();
			}
		}
		if ( @$x_start_mem) { 
			$mem = memory_get_peak_usage(false);
			$mem2 = memory_get_usage(false);
			//echo ((float)$mem/1024)/1024 . " ";
			//echo (float)$mem2/1048576 . " ";
			//echo (float)$x_start_mem/1048576;
		}
		
		
    }

    /**
     * Get a magic member
     * @param $what string the name of the member
     * @return mixed the member value
     */
    public function __get ( $what ) {
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	    $s = new SESSION();
		switch ( $what ) {
            // get a production token
            case 'prod_token':
                return (string)$this->xml->site->environment === 'production'?'prod':'';
            break;
            // need a way to get the lang from the app
            case 'app_lang':
                $class = (string)$this->xml->appname;
                return $this->services->$class->lang;
            break;
            case 'service':
                return array (
                    'login' => LoginService::instance()
                );
            break;
            case 'accepted_language':
                return (string) new accepted_language();
            break;
            case 'eol':
                return $this->is_cli?"\r\n":"<br>\r\n";
            break;
            // are we running from command line?
            case 'is_cli':
                return php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']);
            break;
                case 'performance_tracking':
                    return $this->debug_manager->_is(xo_debug_manager::performance);
            break;
            case 'lang':
                $lang = (string)$this->config->xml()->site->xobjects_language;
                return $lang?$lang:'en';
            break;
            case 'my_username':
                return $s->username?$s->username:($s->email?$s->email:"unknown");
            break;
            case 'is_logged_in': return $this->logged_in(); break;
			case 'request':
				return new REQUEST;
			break;
			// get the timezone offset
			case 'tz_offset':
			case 'timezone_offset':
				$date = new DateTime("now",new DateTimeZone( $this->timezone));
				return $date->getOffset()/3600;
			// get the browser
			case 'browser':
				return $this->services->utilities->browser();
			break;
			// get the usage manager
			case 'usage_manager':
				return usage_manager::instance();
			break;
			// logging
			case 'log_enabled':
				return ((string)$this->config->xml()->logger == 'enabled')?true:false;
			// get the xml_cache
			case 'xml_cache':
				return xml_cache::instance();
			break;
			// returns the apis (api_manager)
			case 'apis':
				return api_manager::instance();
			break;
			case 'app_debug':
                return $this->debug_manager->_is(xo_debug_manager::app_debug);
                break;
			case 'debug':
                return $this->debug_manager->_is(xo_debug_manager::debug);
			break;
			// get managed services
			case 'managed_services':
				return $this->app->managed_services;
			break;
			// is logged in?
			case 'logged_in':
				return $this->logged_in();
			break;
			// get all associated services
			case 'services':
				return new services_manager();
			break;
			case 'debug_level':
				return (int)$this->xml->debugger->level? (int)$this->xml->debugger->level:0;
			break;
			case 'xml':
			
				return $this->config->xml();
				
			break;
		
			case 'auth_service':
			
				return $this->service['login'];
				
			break;
			// get appname
			case 'appname':
				return $this->app->name;
				
			break;
		
			// get the app
			case 'app':
			
				return $this->app( (string) $this->config->xml()->appname );
				
			break;
		
			// config
			case 'config':
			
				return $this->config->xml();
				
			break;
		
		
			// get my user record
			
			case 'me':
			
				return $this->service['login']->me;
				
			break;
		
			case 'user_type':
			
				return $this->service['login']->user_type;
				
			break;
		
			case 'uid':
			
				return $this->service['login']->uid;
				
			break;
		
			case 'vcache_config':
				
				return  (object) $this->config->xml()->vcache_config;
		
			default:
				if ( ! isset( $this->$what )) {
					$msg="<span style=\"color: red;\">$tag->event_format : The application code attempted to access an undefined property <span style=\"font-weight:bold;color:green\">'$what'</span></span>";
					$this->log( xevent::warning, $msg );
					trigger_error( $msg, E_USER_WARNING);
					return false;
				} else
					return $this->$what;
			break;
		}
	}
	
	//! get the current browser as a text token
	public function browser() {
	
		return $this->services->utilities->browser();
	}

    /**
     * @return null
     */
    public static function instance() {
        $xobjects = null;
		// if the instance hasn't been created yet
        if (!isset(self::$instance)) {
            $xobjects = new x_objects;
            self::$instance = $xobjects;
            return self::$instance;
        } else {
            // return a reference to the instance
            return self::$instance;
        }

    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	

	/*! get a specific app
	\param $name (string) The name of the app
	\returns new WebApp
	\throws Exception on errors
	*/
	public function app ( $name ) {
	
		return new WebApp( $name );
		
	}
	
	//! check whether the user is logged in
	public function logged_in () {
	
		return $this->service['login']->is_logged_in();
		
	}
	public function login_as( $user ){
        return $this->set_logged_in($user);
    }
	//! set a user as logged in
	public function set_logged_in( $user ) {
		
		return $this->service['login']->set_logged_in( $user );
	
	}
	
	//! logout current user
	public function logout() {
	
		$this->service['login']->logout();
		
		// to chain commands ;)
		return $this;
		
	}
	
	//! redirect page to another location
	public static function redirect( $where , $returl = null ) { utilities::redirectTo( $where, $returl ); }

	//! returns id of currently logged in user
	public function my_id() {
	
		return $this->service['login']->my_id();
	
	}
	
	//! return a given service object instance
	public function service( $name ) {
	
		return $this->service[$name];
		
	}
	
	//! translate a key to a classname
	public function key_to_class( $key ) {
	
		$arr = explode( '-', $key);
		
		foreach ( $arr as $key => $val ) {
		
			switch ( (string) $this->config->xml()->classname_case ) {
			
				case 'all_lowercase':
				
					$arr[ $key ] = strtolower( $val );
					
				break;
				
				default:
					
					$arr[ $key ] = ucfirst( $val );
					
				break;
				
			}
		}
		
		return implode( '', $arr );
	
	}
	
	//! get a new png image
	public function png_image( $width, $height, $color) {

		return new png_image( $width, $height, $color);
	}	
	
	//! throw a new exception
	public function exception( $type, $tag, $data ){
		// log an event
		$this->log(
			xevent::exception,
			"$tag->event_format $data"
		);
		// throw it
		throw new xexception( $type, $tag, $data );
	}
	/**
	 * log an event to the application's event log
	 * @param int $level indicates the logging level required
	 * @param string $msg the complete logged message
	 */
	public function log($level,$msg) {
		xevent::log( $level, $msg );
	}
	
	/**
	 * send an email using sendmail
	 * @param string $from the from email
	 * @param string $to the to email
	 * @param string $subj the message subject
	 * @param string $body the message body
	 * @param string $headers the optional message headers
	 * @return boolean result of sending the message
	 */
	 public function send_email( $from = null,$to,$subj,$body,$headers = null){
		// keep track of tries
		$tries = 0;
		$result = false;
		// combine headers if necessary
		if ( $from )
			$headers = ($headers)?"From: $from\r\n$headers":"From: $from";
		// try sending a few times
		while ( ! $result && $tries < 3 ) {
			$tries++;
			// send the email and save result
			$result = mail(
				$to,
				$subj,
				$body,
				$headers
			);
		}
		$log = ( $result ) ? 
			array( 
				xevent::success, 
				xo_codetag::create( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__)->event_format . 
				" email sent from $from to $to subject $subj body $body headers $headers") 
				:
			array(
				xevent::failure,
				xo_codetag::create( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__)->event_format . 
				" x-objects gave up after 3 attempts: email not sent from $from to $to subject $subj body $body headers $headers"
			);
		// create a log entry
		$this->log($log[0],$log[1]);
		// return result
		return $result;
	 }
	 
	 //! warn
	 public function warn($msg){
	 	// log an event
	 	$this->log( xevent::warning, $msg);
	 	// display a warning
	 	trigger_error( $msg, E_USER_WARNING);
	 	// great for upstream management of errors
	 	return false;
	 }
	 
	 //! magic call... woo hoo :)
	 public function __call($what,$args){
		switch($what ){
			default:
				$this->warn( "<span style=\"color:red\">An attempt was made to call an undefined function <span style=\"color: green\">$what</span></span>");
			break;			
		}	 	
	 	
	 }
	 
	 //! debug messages
	 public function debug( $tag, $msg){
		if (! $this->debug ) {
			//echo "no debug";
			//exit;
			return;

		}
		if ( $this->debug_file ){
			fputs( $this->debug_file, $tag->event_format . " " . $msg. "\r\n");
			fflush($this->debug_file);
		}
		else
			echo '<div class="x-debug xo-round3">' . "$tag->event_format : $msg</div>\r\n";
	 }

    /**
     * run a cron job by name, looks for the job as a class or function
     * @param string $key the name of the job to run
     * @return bool true if run successfully, false otherwise
     */
    public function run_cron_job( $key , $verbose = false ){
        $r = false;
        if ( ! class_exists($key))
            $this->last_error = "$key: no such class exists";
        else{
            $job = new $key();
            if ( ! $job instanceof xo_cronjob)
                $this->last_error = "$key: must implement xo_cronjob";
            else
                $r = $job->run($verbose);
        }
        return $r;
    }

    /**
     * get a userstring
     */
    public function userstring($key){
        // first determine language
        $l = $this->xml->site->xobjects_language;
        // try to get app service lang
        $app_class = $this->xml->appname;
        $app = $this->services->$app_class;
        if ( $app)
            $l = $app->lang?$app->lang:$l;
        switch( $key){
            case 'choose':
                return ($l=='es' || $l == 'es_CO')?"Seleccionar...":"Please choose...";
            break;
        }
        return "";
    }

    // add performance tracking count
    public function performance($stat,$value){
        if ( $this->performance_tracking)
            $this->performance->performance($stat,$value);
    }

    public function platform(){
        return $this->platform;
    }

    /**
     * css style for showing a banner with the label of the environment
     */
    public function environment_banner_style(){
        $style = 'display:none;';
        $settings = $this->config->xml()->site->environment;
        if ($settings)
            $style = $settings->display == 'yes'?'display:block':'display:none';
        return $style;
    }

    public function environment_label(){
        $label = 'Environment: Unknown';
        $settings = $this->config->xml()->site->environment;
        if ($settings)
            $label = $settings->label?'Environment: '.(string)$settings->label : 'Environment: unknown';
        return $label;
    }
}

?>