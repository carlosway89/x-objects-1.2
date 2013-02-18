<?php
/*
 * Created on 17/06/2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class human_time extends magic_object {
    // resources for different languages
    private static $resources = null;
	
	public function __construct( $timestamp ){ 		
    	global $container,$xobjects_location;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        //echo "timestamp =".$timestamp;
        // if necessary load resources
        if (! self::$resources )self::$resources = new xo_resource_bundle("human_time");

      	// by default the human time is just now
    	$this->human_time = self::$resources->strings['just_now']?self::$resources->strings['just_now'] :"just now";

        if (! $timestamp){
            $this->human_time = self::$resources->strings['never']?self::$resources->strings['never'] :"never";
            return;
        }
    	$now_time = time();

        $diff = $now_time - $timestamp;
        
        $now_str = date('H:i:s m-d-Y',$now_time);
        $time = date('H:i:s m-d-Y',$timestamp);
		if ( $container->debug)
			echo "$tag->event_format: timestamp=$time,now=$now_str,diff=$diff<br>\r\n";
		if ( $container->debug && $diff < 0)
			echo "$tag->event_format: time occurs in the future<br>\r\n";
			
        if ( $diff < 5)
        	$this->human_time = self::$resources->strings['just_now']?self::$resources->strings['just_now'] :"just now";
        elseif( $diff < 20)
        	$this->human_time = self::$resources->strings['seconds_ago']?self::$resources->strings['seconds_ago'] :"a few seconds ago";
        elseif ( $diff < 60)
      		$this->human_time = self::$resources->strings['less_minute']?self::$resources->strings['less_minute'] :"less than a minute ago";
        elseif ( $diff < 3600 ){
        	$val = round( $diff/60);
        	$p = $val > 1? "s":"";
        	$this->human_time = self::$resources->strings['minutes_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['minutes_ago'])) :"$val minute$p ago";
        	
        }
        elseif ( $diff < 86400 ){
        	$val = round( $diff / 3600);
        	$p = $val >1 ? "s":"";
        	$this->human_time = self::$resources->strings['hours_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['hours_ago'])) :"$val hour$p ago";
        }
        elseif ( $diff < 604800 ) {
            $val = round( $diff / 86400);
            $p = $val >1 ? "s":"";
            $this->human_time = self::$resources->strings['days_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['days_ago'])) :"$val day$p ago";
        }
        elseif ( $diff < 2592000){
            $val = round( $diff / 604800);
            $p = $val >1 ? "s":"";
            $this->human_time = self::$resources->strings['weeks_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['weeks_ago'])) :"around $val week$p ago";
        }
        elseif ( $diff < 31536000){
            $val = round( $diff / 2592000);
            $p = $val >1 ? "s":"";
            $this->human_time = self::$resources->strings['months_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['months_ago'])) :"around $val month$p ago";
        }
        else {
            $val = round( $diff / 31536000);
            $p = $val >1 ? "s":"";
            $this->human_time = self::$resources->strings['years_ago']?preg_replace( '/#p/',$p,preg_replace( '/#val/',$val,self::$resources->strings['years_ago'])) :"around $val year$p ago";

        }
	}
	
	public function __toString(){
		return $this->human_time;
	}
}

?>
