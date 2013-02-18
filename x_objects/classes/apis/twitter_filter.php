<?php
/*
 * Created on 11/03/2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class twitter_filter extends magic_object {
 	
 	public function __construct( $arr ){
 		$this->arr = $arr;
 	}
 	
 	//! try matching it
 	public function match( $tweet) {
 		global $container;
 		$matched = false;
 		foreach ( $this->arr as $name => $filter) {
 			if ( ! $container->services->utilities->paren_balanced( $filter))
 				echo "$filter is not balanced<br>\r\n"; 
 			if ( preg_match( $filter, $tweet->text)){
 				$matched = $name;
				break; 				
 			}
 		}
 		return $matched;
 	}
 	
 }
?>
