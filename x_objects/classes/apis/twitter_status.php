<?php
/*
 * Created on 10/03/2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class twitter_status extends magic_object {
 	private $xml = null;
 	public function __construct( $xml ){
 		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
 		$this->xml = $xml;
		//echo $this->xml->asXML();
// 		$container->log(xevent::debug, "$tag->event_format : new status user ". $this->xml->user->id . " ". $this->xml->user->screen_name);
 	}
 	
 	//! magic get
 	public function __get( $what ){
 		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
 	
 		switch ( $what){
 			// get the users xml
 			case 'user_xml':
 				$xml = ( $this->xml->getName()== 'direct_message')? $this->xml->sender : $this->xml->user;
 			//	$container->log( xevent::twitter_debug , "$tag->event_format : user xml is " . $xml->asXML());
 				return $xml;
 			break;
 			// get the user
 			case 'user':
 				return new follower("twitter_id='$this->user_id'");
 			break;
 			// get the time zone offset
			case 'user_tz_offset':
				$offset = $this->user_xml->utc_offset/3600;
			//	$container->log( xevent::twitter_debug, "$tag->event_format : utc offset for status user is $offset");
				return $offset;
			break;
 			case 'user_id':
// 				echo $this->xml->asXML();
 				$id = ($this->xml->getName() == 'direct_message')? (int)$this->xml->sender->id : (int)$this->xml->user->id;
 				//$container->log( xevent::twitter_debug, "$tag->event_format : twitter user id is $id");
 				return $id;
 			break;
 			case 'is_follower':
 				global $container;
 				$api = $container->apis->twitter;
 				return ($api->is_follower( $this->user_id));
 			break;
 			case 'string_representation':
 				return (string)$this->xml->text;
 			break;
 			default:
 				if ( preg_match( '/user_([a-z_]+)/', $what, $matches)) { 
 					$value = ($this->xml->getName() == 'direct_message')?
 						(string)$this->xml->sender->$matches[1]: 
 						(string)$this->xml->user->$matches[1];
 			//		$container->log( xevent::twitter_debug , "$tag->event_format : user value = $value");
 					return $value;
 				}
 				else
 					return $this->xml->$what;
 			break;
 		}
 		
 	}
 	
 }
?>
