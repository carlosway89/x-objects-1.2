<?php
/*
 * Created on 08/03/2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class twitter_api extends magic_object {
 	//! the configuration
 	private $config = null;
 	//! the connection
 	private $connection = null;
 	//! link to the container
 	private $container = null;
 	
 	/** @param object follower_cache in memory cache to quickly check for followers based on prior results
 	 * 
 	 */
 	private static $follower_cache = null;
 	
 	/** @param object api_persistence business object with persistence information for API
 	 * 
 	 */
 	private static $api_persistence = null;
 	
 	
 	//! construct looking for a twitter configuration
 	public function __construct($config_key = "twitter_config"){
 		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	 	$this->container = $container;
 		// try to get the configuation
 		$this->config = $container->xml_cache->$config_key;
 		if ( ! $this->config)
 			$container->exception( 1, $tag, "twitter could not find $config_key in XML Cache, make sure global variable exists" );
 		if ( $container->debug || $this->config->debug)
 			echo "$tag->event_format : twitter debugging enabled<br>\r\n";
 		// Set the authorization values
  		// In keeping with the OAuth tradition of maximum confusion, 
  		// the names of some of these values are different from the Twitter Dev interface
  		// user_token is called Access Token on the Dev site
  		// user_secret is called Access Token Secret on the Dev site
  		// The values here have asterisks to hide the true contents 
  		// You need to use the actual values from your Twitter app
  		$this->connection = new tmhOAuth(array(
    		'consumer_key' => (string)$this->config->consumer_key,
    		'consumer_secret' => (string)$this->config->consumer_secret,
    		'user_token' => (string)$this->config->user_token,
    		'user_secret' => (string)$this->config->user_secret,
    		'debug'=>$this->config->debug
  		)); 
  		// initialize the follower's cache with up to 10 elements
  		self::$follower_cache = ( self::$follower_cache) ? self::$follower_cache : new x_cache( 10 );
  		// retrieve persistence information
  		$this->api_persistence = new xo_twitter_api("id='1'");
  		if ( $container->debug) echo "$tag->event_format: api persistence id = " .$this->api_persistence->last_id_mentions . "<br>\r\n";
  		if ( ! $this->api_persistence->exists)
  			$this->container->exception( 1, $tag, "the API could not retrieve its persistence information.  We recommend checking the database for a proper table and record.");
  		//else $this->container->log( xevent::twitter_success , "$tag->event_format : The Twitter API has startup up successfully");	
 	}
 	
 	// save persistence when destructing
 	public function __destruct(){
 		
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
 		
 		// if the oauth or rest call is below performance, log it
 		//if ( $this->oauth_below_performance && $this->api_persistence->num_oauth_calls % 5 == 0)
 			//$this->container->log( xevent::twitter_warning, "$tag->event_format : Twitter Oauth network response rate ($this->oauth_response_rate) is below performance ($this->oauth_performance_response_rate)");
 		if ( $this->rest_below_performance && $this->api_persistence->num_rest_calls % 5 == 0 )
 			$this->container->log( xevent::twitter_warning, "$tag->event_format : Twitter REST API network response rate ($this->rest_response_rate) is below performance ($this->rest_performance_response_rate)");
 		
 		if ( $this->api_persistence->save() ) {
 			if ( $this->container->debug) echo "$tag->event_format : Twitter API persistence data successfully saved<br>\r\n";
 		}
 		else {
 			if ( $this->container->debug) echo "$tag->event_format : Twitter API persistence data NOT saved due to an error<br>\r\n";
 			$this->container->log( xevent::twitter_warning , "$tag->event_format : The Twitter API failed to persist; this may cause problems during the next run.".$this->api_persistence->save_error);	
 		}
 		if ( $this->container->debug) echo "$tag->event_format : the Twitter API successfully destroyed<br>\r\n";
 	}
 	
 	public function __get ( $what ) {
		global $container;
		$map = array( 
			"my_tweets" => "1/statuses/home_timeline",
			"my_mentions"  => "1/statuses/mentions",
			"my_followers_mentions"  => "1/statuses/mentions",
			
			"my_messages" => "1/direct_messages",
			"followers"=> "1/followers/ids");
			
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		if ( $container->debug || $this->config->debug)
 			echo "$tag->event_format : getting $what <br>\r\n";
 		
		switch ( $what ) {
			case 'signed_in_user':
				$cookie = COOKIE::create()->twitter_anywhere_identity;
				if ( $cookie){
					$parts = explode( ':',$cookie);
					return sha1( $parts[0].$this->config->consumer_secret) == $parts[1]?$parts[0]:null;					
				} else return null;
			break;
				
			case 'followers':
				// Make the API call
  				$this->connection->auto_fix_time_request(
					'GET', 
    				$this->connection->url($map[$what],'xml'),
    					array( 
					"screen_name" => (string)$this->config->screen_name // use since id to avoid duplication
    				) 
    			);
    			$response = $this->response();
    			//echo $response;
    			return $response;
    		break;
			case 'rest_performance_response_rate':
				return $this->config->performance->rest_failure_threshold;
			break;
			case 'rest_response_rate':
				if ( ! $this->api_persistence->num_rest_calls) return 0;
				$actual = 1 - ($this->api_persistence->num_failed_rest_calls) / ($this->api_persistence->num_rest_calls);
				return (float)number_format( $actual,2);
			break;
			// is oauth network response below performance
			case 'rest_below_performance':
				return ( $this->rest_response_rate < $this->rest_performance_response_rate)? true: false;
			break;
			case 'oauth_performance_response_rate':
				return $this->config->performance->oauth_failure_threshold;
			break;
			case 'oauth_response_rate':
				$actual = 1 - ($this->api_persistence->num_failed_oauth_calls) / ($this->api_persistence->num_oauth_calls);
				return (float)number_format( $actual,2);
			break;
			// is oauth network response below performance
			case 'oauth_below_performance':
				return ( $this->oauth_response_rate < $this->oauth_performance_response_rate)? true: false;
			break;
			// determine last command
			case 'last_command':
				return $this->last_command();
			break;
			case 'my_mentions':
			case 'my_messages':
			case 'my_followers_mentions':
				$which = (preg_match('/messages/',$what))?"direct_messages":"mentions";
				// Make the API call
  				$this->connection->auto_fix_time_request('GET', 
    				$this->connection->url($map[$what],'xml'),
    				array( 
					"count" => 20,								// get 20 messages
    				"since_id" => $this->since_id($which)		// use since id to avoid duplication
    				) 
    			);
    			$followers = ( preg_match('/followers/',$what)) ? true : false;
    			//print_r( $this->connection->response);
				return $this->response($followers);  
			break;
			// pretty cool eh? :-)
			case 'my_mentions_and_messages':
			case 'my_followers_mentions_and_messages':
				if ( $container->debug ||  $this->config->debug)
					echo "$tag->event_format : getting mentions and messages<br>\r\n";
					//$followers = ( preg_match('/followers/',$what)) ? true : false;
    			$mentions = (preg_match('/followers/',$what))? $this->my_followers_mentions: $this->my_mentions;
				$messages = $this->my_messages;
				if ( $this->config->debug)
					echo "$tag->event_format : found ".count($mentions). " mentions and ".count($messages). " messages<br>\r\n";
				//echo $mentions;
				//print_r( $mentions);
				//print_r($messages);
				return array_merge(
					is_array( $mentions)?$mentions:array(),
					is_array( $messages)?$messages:array()
				);
				
			break;
			default:
				return parent::__get($what);
			break;
			
		}
	}
	
	/**
	 * @method mixed returns a parsed API response
	 * @return mixed returns an array of objects if successful, false if unsuccessful
	 */
	 private function response($followers=false){
	 	global $container;
	 	$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

	 	$last_command = $this->last_command;
	 	$code = $this->connection->response['code'];
		if ( $container->debug || $this->config->debug)
 			echo "$tag->event_format : last command was $last_command, response code is $code<br>\r\n";
 		
		// log call for performance measures
		$this->api_persistence->num_rest_calls = (int)$this->api_persistence->num_rest_calls + 1;
		
	 	switch( $this->connection->response['code']){
	 		// a server error occurred
	 		case 503:
	 			$this->container->log( xevent::twitter_error, "$tag->event_format : Twitter.com returned a server script error (503).  This is most likely due to the server being temporarily overloaded");
				return false;
	 		// forbidden
	 		case 403:
	 			$this->container->log( xevent::twitter_warning, "$tag->event_format : the API call is forbidden(403): ". $this->connection->response['response']);
				return false;
	 		break;
	 		// failed network
	 		case 0:
				// log call for performance measures
				$this->api_persistence->num_failed_rest_calls = (int)$this->api_persistence->num_failed_rest_calls + 1;
	 			$this->container->log( xevent::twitter_warning, "$tag->event_format : the API call failed, possibly due to a network issue (0):".$this->connection->response['response']);
	 			return false;
			break;	 		
	 		// ok!
	 		case 200:
	 		if ( $container->debug || $this->config->debug)
 				echo "$tag->event_format : API return success (200)<br>\r\n";
 		
	 			if ( is_object( $this->filter )) {
	 				if ( $container->debug || $this->config->debug )
	 					echo "$tag->event_format : using a filter for tweets : $this->filter<br>\r\n";	 				
	 			} else {
	 				if ( $container->debug || $this->config->debug )
	 					echo "$tag->event_format : NO filter provided for tweets<br>\r\n";	 				
	 			}
	 			$results = array();
	 			if ( $container->debug || $this->config->debug) {
	 				//print_r( $this->connection->response);
	 				echo $this->connection->response['response'];
	 				
	 			}
	 			$xml = simplexml_load_string( $this->connection->response['response']);
	 			
	 			if ( $container->debug || $this->config->debug)
 					echo "$tag->event_format : response was " . $this->connection->response['response'] ."<br>\r\n";
 				if ( $last_command == 'followers')
 					return $xml;
	 			if ( is_object( $xml )) {
	 				//echo $xml->asXML();
	 				$count = count( $xml->children());
					if ( $container->debug || $this->config->debug)
						echo "$tag->event_format : got back $count actual tweets<br>\r\n";
	 				/*
	 				 * i've seen some weirdness here recently, so if we didn't get bck anything add more logging
	 				 */
	 			//	 $container->log( xevent::twitter_notice, "$tag->event_format : return XML is ". $xml->asXML());
	 				
	 				$high_id = 0;
	 				foreach( $xml->children() as $status) {
	 					if ( $container->debug || $this->config->debug )
	 						echo "$tag->event_format : got back tweet id=$status->id<br>\r\n";
	 					$high_id = ($high_id > (string)$status->id)? $high_id : (string)$status->id;
	 					if ( $this->filter && $this->filter->match( $status)){
	 						if ( $container->debug || $this->config->debug )
	 							echo "$tag->event_format : status matches a filter<br>\r\n";
	 						
	 						$tweet = new twitter_status( $status);
	 						if ( $followers ) { 
	 							if ( $container->debug || $this->config->debug)
	 								echo "$tag->event_format : applying rules for followers<br>\r\n";	 				
	 							
	 							if ( $tweet->is_follower) {
	 								$xml = ($status->getName() == 'direct_message')? $status->sender : $status->user;
	 								// create a new follower
	 								follower::create_from_xml( $xml); 
	 								array_push( $results, new twitter_status( $status));
	 								// process rules for before returning results
	 								$this->process_rules_for("before",$tweet);
	 							}
	 							else {
	 								//$container->log( xevent::twitter_debug , "$tag->event_format : processing rules for non follower $tweet->user_id");
	 								$this->process_rules_for( "non_followers", $tweet);
	 							}
	 						} else array_push( $results, new twitter_status( $status));
	 					} else {
	 						if ( $container->debug || $this->config->debug)
	 							echo "$tag->event_format : no filter or no match<br>\r\n";
	 					}
	 				}
	 				//echo "$tag->event_format : got back HIGHEST tweet id=$high_id<br>";
	 				if ( $high_id != '' && (int)$high_id != 0 ){
	 					$member = "last_id_$last_command";
	 					//echo "$member<br>";
	 				//	$this->api_persistence->$member = (string)$high_id;
	 				}
	 					
	 			}
				//$this->container->log( xevent::twitter_success, "$tag->event_format : the API call was successful (200) and returned ".count($results) . " records");
	 			return (count($results)>0)?array_reverse( $results ):null;	 			
	 		break;
	 		// not authorized
	 		case 401:
	 			// generate a warning
	 			$this->container->log( xevent::warning, "$tag->event_format : the API call was unauthorized (401):".$this->connection->response['response'] .".  We recommend you check your twitter configuration settings, especially your OAuth parameters.");
	 			return false;
	 		break;
	 		default:
	 			// generate a warning
	 			if ( $container->debug || $this->config->debug)
	 				echo  "$tag->event_format : the API call response was not recognized: ".$this->connection->response['code'] . " " . $this->connection->response['response'] ."<br>\r\n";
	 			return false;
	 		break;
	 	}
	 	
	 }
	 
	 /**
	  * filter tweets using a twitter filter criteria
	  */
	 public function filter( $filter ){ $this->filter = $filter;
	  }
	 	
	 /**
	  * magic set
	  */
	 public function __set( $what, $how) { parent::__set( $what, $how); }

	/**
	 * is_follower( $id): determines if a given user is a follower of the system twitter account
	 * 
	 * Uses 3-tier caching to speed up the process of checking
	 */	
	public function is_follower( $id ) {
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		//$this->container->log( xevent::twitter_debug, "$tag->event_format : checking if $id is a follower");
			
		/**
		 * first step is to see if exists in follower cache
		 */
		if ( self::$follower_cache->has( (int)$id ) ) { 
		//	$this->container->log( xevent::twitter_success, "$tag->event_format : $id IS a follower, discovered using cache");
			return true;
		}
		
		// else see if the user exists locally
		$follower = new follower("twitter_id='$id'");
		if ( $follower->exists) { 
			self::$follower_cache->add( (int)$id );
			//$this->container->log( xevent::twitter_success, "$tag->event_format : $id IS a follower, found in database");
			return true;
		}
		else {
			
			// set up which to use for a
			$type_a = (is_numeric($id))? "user_id_a" : "screen_name_a";
			// use the API
			// Make the API call
  			$this->connection->auto_fix_time_request('GET', 
    			$this->connection->url("1/friendships/exists",'xml'),
    			array( $type_a => "$id", "screen_name_b" => "postmyscore") 
    			);
    		if ( (int)$this->connection->response['code'] == 200) {
    			//print_r($this->connection->response['response']);
    			if ( preg_match( '/true/',$this->connection->response['response'])) {
    			//	$this->container->log( xevent::twitter_success, "$tag->event_format : $id IS a follower:". $this->connection->response['response']);
    				self::$follower_cache->add( (int)$id );
					return true;	
    			} else { 
    				//$this->container->log( xevent::twitter_notice, "$tag->event_format : twitter id $id is NOT a follower( return value is ".$this->connection->response['response']." )");
    				return false;
    			}
    		} else {
//    			$this->container->log( xevent::twitter_failure, "$tag->event_format : failed to get follow status for $id ");
    			
    			return false;
    		}
		}
	}  
	
	//! processs business rules for certain conditions
	private function process_rules_for( $type, $tweet){
		global $container; 
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		$code = $container->services->utilities->random_password(5);
		
		if ( $container->debug || $this->config->debug)
			echo "$tag->event_format : processing rules for $type<br>\r\n";
		
		switch( $type){
			case 'non_followers':
				if ( $this->config->rules->non_followers)
					foreach ( $this->config->rules->non_followers->children() as $rule)
						switch ( $rule->getName()) {
							// begin following the user
							case 'follow':
								$this->follow( $tweet->user_id );
							break;
							case 'mention':
								$text = (string) $rule;
//								$this->container->log( xevent::debug , "$tag->event_format : mention $text");
								/*
								 * there seems to be an issue where the own user is being tracked
								 */
								if ( $tweet->user_screen_name != "postmyscore")
									$this->tweet("@$tweet->user_screen_name $text ($code)");
							break;
						}
				else $this->container->log( xevent::twitter_warning, "$tag->event_format : $type didn't match any rules'");
			break;
			case 'before':
				if ( $this->config->rules->before)
					foreach ( $this->config->rules->before->children() as $rule)
						switch( $rule->getName()){
							case 'method':
								call_user_func( (string)$rule,$tweet);
							break;
						}
		}
	}
	
	//! get user details as XML
	public function get_user( $id ){
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		// set element based on id type
		$type = (is_numeric($id))? "user_id" : "screen_name";
		// Make the API call
  		$this->connection->auto_fix_time_request('GET', 
    		$this->connection->url("users/show","xml"),
    			array( $type => $id) 
    			);
    		switch ( (int)$this->connection->response['code']){
    			case '200':
    				return simplexml_load_string($this->connection->response['response'] );
    			break;
    			default:
    				return null;
    			break;
    		}
	}
	
	//! post a tweet
	public function tweet( $update){
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// Make the API call
  		$this->connection->auto_fix_time_request('POST', 
    		$this->connection->url("statuses/update","xml"),
    		array( "status" => $update) 
    			);
    		switch ( (int)$this->connection->response['code']){
    			case '403':
    				if ( preg_match( '/duplicate/', $this->connection->response['response'] ))
    					$this->container->log( xevent::twitter_warning , "$tag->event_format : attempt to post a duplicate status $update");
    			break;
    		}
    		//echo $this->connection->response['code'] . " ". $this->connection->response['response'] . "<br>";
//    			print_r( $this->connection->response);
				//return $this->response($followers);  
		 	return (int)$this->connection->response['code'];
	}
	
	//! follow a user
	public function follow( $id ){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		/*
		 * user is implicitly following himself!
		 */
		if ( $id == 521699653 )
			return true;
		// Make the API call
  		$this->connection->auto_fix_time_request('POST', 
    		$this->connection->url("friendships/create","xml"),
    			array( "user_id" => $id) 
    		);
    		switch ( (int)$this->connection->response['code']){
    			case '403':
    				// log it
    				$container->log( xevent::twitter_warning, "$tag->event_format : unable to follow twitter id $id return code 403 ". $this->connection->response['response']);
    				return false;
    			break;
    			case '200':
    				// log it
    				$container->log( xevent::twitter_success, "$tag->event_format : now following twitter id $id ");
    				return true;
    			break;
    		}
	}
	
	//! determine last command
	private function last_command(){
		if ( preg_match('/mentions/',$this->connection->response['info']['url']))
			return 'mentions';
		elseif ( preg_match('/direct_messages/',$this->connection->response['info']['url']))
			return 'direct_messages';
		elseif ( preg_match('/followers/',$this->connection->response['info']['url']))
			return 'followers';
		else return null;
	}
	
	//! get the since id
	private function since_id( $which ){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		$member = "last_id_$which";
		//$container->log( xevent::twitter_debug , "$tag->event_format : since id for $which is " . (string)$this->api_persistence->$member );
		return (string)$this->api_persistence->$member;
	}
	
	//! log an oauth call
	public function log_oauth_call(){
		$this->api_persistence->num_oauth_calls = (int)$this->api_persistence->num_oauth_calls + 1;
		
	}

	//! log an oauth call
	public function log_failed_oauth_call(){
		$this->api_persistence->num_failed_oauth_calls = (int)$this->api_persistence->num_failed_oauth_calls + 1;
	}
	
	/**
	 * get signed in user (if any)
	 */
 }
?>
