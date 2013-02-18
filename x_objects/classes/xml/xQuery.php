<?php

//! xquery parsing engine
class xQuery {

	// regex for a member name
	//const MEMBER_NAME = '[a-z|A-Z|_|0-9\[\]]+';
	const MEMBER_NAME = '[a-z|A-Z|_|0-9]+';
	
	const MEMBER_NAME_WITH_ARGS = '([a-z|A-Z|_|0-9\[\]]+)\(([a-z|A-Z|_|0-9\[\]]*)\)';
	const MEMBER_ARG = '[a-z|A-Z|_|\/|0-9\[\]]+';
	

	// debugging
	private static $debug = false;
	
	//! escape special characters
	public static function x_escape( $str, $left_bracket = false ) {
	
		$str = preg_replace( '/\]/' , '\\]' , $str );
		
		$str = preg_replace( '/\(/' , '\\(', $str);
		$str = preg_replace( '/\)/' , '\\)', $str);
		
		$str = preg_replace( '/\./' , '\\.' , $str);
		
		$str = preg_replace( '/\//' , '\\/' , $str);
		
		
		
		if ( $left_bracket ) {
			$str = preg_replace( '/\[/' , '\\[' , $str );
			
		}
		return $str;
	}

	//! check and parse all rules
	public static function parse( $str , $obj = null , $attrs = null ) {
		global $container,$xobjects_location,$directory_name;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		if ( $container->debug)		echo "$tag->event_format: passed object of type = " . get_class( $obj ) . "<br>";

		// case -1: login service
		$regex = '/\[login-service:([a-z|_]+)\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
		
			//print_r( $matches );
			
			$newmatch = '/\\' . self::x_escape( $matches[0][0] ) . '/';
	
			$str = preg_replace( $newmatch, x_objects::instance()->service['login']->$matches[1][0] , $str );
			
	
		}
				
		// case 0: handle embedded passed values by reference from the parent xml
		$regex = '/\[@[A-Z|a-z|_|-| |0-9]+\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
			foreach ( $matches[0] as $match ) {
				//echo $match . ' ';
				$token = trim ( $match, '@[]');
				//echo $token;
				$str = preg_replace( "/\[@$token\]/", $attrs[ $token ], $str);
			}
		}
		

		
	
		// case 1: embedded get member
		$regex = '/\[get:' . self::MEMBER_NAME . '\]/';

		if ( preg_match_all( $regex, $str, $matches ) ) {
			if ( $container->debug) {
				echo "$tag->event_format : found [get] in $str<br>\r\n";
			}
			foreach( $matches[0] as $match ) {
				$member = rtrim( substr( $match , 5 ) , ']');
				//echo "match = $match , member = $member" . '<br>';
				$newmatch = '/\\' . self::x_escape( $match ) . '/';
				//echo get_class() . "$newmatch value = " . $obj->$member . "<br>";
					
				// get value based on whether the obj is a business object or SimpleXML
				$value = null;
				if ( is_object($obj))
					$value = ( get_class( $obj) == 'SimpleXMLElement' ) ? $obj[$member] : $obj->$member;
				elseif( is_array($obj))
					$value = @$obj[$member];
                if ( $container->debug) echo "$tag->event_format : replacing $newmatch with $value in $str and dollar zero is $0<br>\r\n";
                $value = preg_replace( '/\$/','\$',$value);
                $str = preg_replace( $newmatch , preg_quote($value) , $str );
                $str = stripslashes($str);
                if ( $container->debug) echo "$tag->event_format : resulting string is $str<br>\r\n";
                //echo $str;
				//echo  "$tag->event_format : xquery [get:member] member = $member, newmatch = $newmatch"
				//. " value = $value str = $str\r\n<br>";
			}
		}
		

		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
								
		// case 2: global and page variables
		// IMPORTANT: doesn't show an error if undefined in scope
		$regex = '/\[var:' . self::MEMBER_NAME . '\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
			if ( self::$debug ) {
				//print_r ( $matches[0] );  echo '<br>';
			}
			foreach( $matches[0] as $match ) {
				$member = rtrim( substr( $match , 5 ) , ']');
				//echo $member;
				//if ( self::$debug ) echo "match = $match , member = $member" . '<br>';
				$newmatch = '/\\' . self::x_escape( $match ) . '/';
				//if ( self::$debug ) echo "newmatch = $newmatch <br>";
				global $$member;
				if ( isset( $$member) && ! ( is_string( $$member) || is_numeric( $$member) )) {
					$container->warn( "$tag->event_format : " . $$member . " is not a string or number and cannot be used as the replacement value of the xQuert expression $match");
				} else
					$str = preg_replace( $newmatch , $$member , $str );
				if ( $container->debug ) {
					//$container->log( xevent::debug, "$tag->event_format : regex=$regex,str=$str,match=$match,member=".$$member);	
					echo "$tag->event_format: regex=$regex,str=$str,match=$match,member=".$$member;
				}
			}
		}


		// case 3: REQUEST var
		$regex = '/\[REQ:' . self::MEMBER_NAME . '\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
			if ( self::$debug ) {
				//print_r ( $matches[0] );  echo '<br>';
			}
			foreach( $matches[0] as $match ) {
				$member = rtrim( substr( $match , 5 ) , ']');
				//if ( self::$debug ) echo "match = $match , member = $member" . '<br>';
				$newmatch = '/\\' . self::x_escape( $match ) . '/';
				//if ( self::$debug ) echo "newmatch = $newmatch <br>";
				$str = preg_replace( $newmatch , @$_REQUEST[ $member ] , $str );
			}
		}


		// case 4: SESSION var
		$regex = '/\[SES:' . self::MEMBER_NAME . '\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
			if ( self::$debug ) {
				//print_r ( $matches[0] );  echo '<br>';
			}
			foreach( $matches[0] as $match ) {
				$member = rtrim( substr( $match , 5 ) , ']');
				if ( self::$debug )
					echo "match = $match , member = $member" . '<br>';
				$newmatch = '/\\' . self::x_escape( $match ) . '/';
				if ( self::$debug )
					echo "newmatch = $newmatch <br>";
				$str = preg_replace( $newmatch , @$_SESSION[ $member ] , $str );
			}
		}

		

		// case 5: local object member
		$regex = '/\[obj:(' . self::MEMBER_NAME . '){1}\.(' . self::MEMBER_NAME . '){1}\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
			if ( self::$debug ) {
				print_r ( $matches[0] );  echo '<br>';
			}
			foreach( $matches[0] as $match ) {
				$member = rtrim( substr( $match , 5 ) , ']');
				if ( self::$debug )
					echo "match = $match , member = $member" . '<br>';
					
				$pair = explode( '.' , $member);
				$obj = $pair[0];
				$member_name = $pair[1];
				if ( self::$debug )
					echo "$obj . $member_name <br>";
					
				if ( preg_match( '/\[[0-9]+\]/', $str ) )
					$newmatch = '/' . self::x_escape( $match , true) . '/';
				else
					$newmatch = '/\\' . self::x_escape( $match ) . '/';
				global $$obj;
				
				if ( ! isset( $$obj ) || ! is_object( $$obj)) {
					$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
					echo '<div class="debug debug-warning" style="color: blue; font-weight: bold;float: left; clear:both;">'."$tag->event_format : ".'<span style="color:green;">'."$obj</span> is not set as an object in global scope</div>";
				} else
					$str = preg_replace( $newmatch , $$obj->$member_name , $str );
			}
		}
		
		// case 6: local object method with args
		$regex = '/\[obj:(' . self::MEMBER_NAME . '){1}\.(' . self::MEMBER_NAME_WITH_ARGS . '){1}\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
		
			//print_r ( $matches );
			$method = $matches[3][0];
			$arg = $matches[4][0];
			$obj = $matches[1][0];
			if ( preg_match( '/\[[0-9]+\]/', $str ) )
					$newmatch = '/' . self::x_escape( $matches[0][0], true) . '/';
				else
					$newmatch = '/\\' . self::x_escape( $matches[0][0] ) . '/';
			//echo $newmatch;
			global $$obj;
			
			$str = preg_replace( $newmatch , $$obj->$method( $arg ) , $str );
		
		}
		
		// case 8: auto check for input checkbox
		if ( preg_match( '/checked="\[autocheck:([a-z|0-9|_]+)\]"/' , $str , $matches ) ) {
		
			$replace = @$obj->$matches[1] ? 'checked="checked"' : '';
			
			$newmatch = '/' . self::x_escape( $matches[0] , true) . '/';
			//echo $newmatch;
			
			$str = preg_replace ( $newmatch , $replace, $str);
		}
		
		// obj member with multiple args

		$multi_arg_member = '(' . self::MEMBER_NAME . ')\((' . self::MEMBER_ARG . '){1}(\,(' . self::MEMBER_ARG . '))*\)';
		$regex  = '/\[obj:(' . self::MEMBER_NAME . '){1}\.(' . $multi_arg_member . '){1}\]/';
		if ( preg_match_all( $regex, $str, $matches ) ) {
		
			
			for ( $j = 0 ; $j < count( $matches[0]) ; $j++ ) {
			
				$objName = $matches[1][$j];
				//echo "objName = $objName<br>";
				global $$objName;
			
				$method = $matches[3][$j];
				//echo "method = $method<br>";
				
			
				$args = array();
				$i = 4;
			
				while ( isset( $matches[$i][$j] ) ) {
			
					if ( @$matches[$i][$j][0]  != ',' )
						array_push( $args , $matches[$i][$j] );
			
					$i++;
				}
			
			
				// call it
				$value =  call_user_func_array( array( $$objName, $method ), $args);
				$newmatch	= '/' . self::x_escape( $matches[0][$j] , true) . '/';
			
			
				$str = preg_replace( $newmatch, $value , $str );
				
			}
			
		}
		
		if ( $container->debug )
			echo "$tag->event_format: xquery string = $str, returning it now...<br>\r\n";
		return $str;
		

	
	}

}

?>