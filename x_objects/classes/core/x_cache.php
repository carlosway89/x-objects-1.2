<?php
/*
 * Created on 15/03/2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class x_cache extends magic_object {
 	private $cache = null;
 	/** construct with a size limit
 	 * 
 	 */
 	public function __construct( $size = 10){
 		$this->cache = array();
 		$this->size = $size;
 	}
 	
 	//! add a new member
 	public function add( $thing){
 		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		// first check if already in it
 		if ( in_array( $thing, $this->cache))
 			return true;
 		else { 
 			//$container->log( xevent::warning, "$tag->event_format: $thing is not a member of cache " . implode(',',$this->cache). " adding it");
 			if ( count($this->cache) >= $this->size){
 				// set random index
 				srand();
 				$index = rand(0,$this->size-1);
 			$this->cache[ $index] = $thing;
 			} else array_push( $this->cache, $thing);
 		}
 	}
 	
 	//! check if cache has something
 	public function has( $thing){
 		global $container;
 		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		//$container->log( xevent::debug, "$tag->event_format : cache = " . implode(',',$this->cache));
 		return in_array( $thing, $this->cache);
 	}
 }
?>
