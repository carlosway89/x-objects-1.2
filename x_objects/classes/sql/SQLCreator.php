<?php
/*
 * Project:			Platform
 * Module:			classes/SQLCreator
 * 
 * Purpose:			static methods for constructing SQL
 * 
 * Created By:		David Owen Greenberg <david.o.greenberg@gmail.com>
 * On:				13 Oct 2010
 */

final class SQLCreator{
	
	private static $Debug = false;
	
	const NAME  = 0;
	const VALUE  = 1;
	
	//! construct a where clause, synonym for below method
	public static function WHERE( $conditions ) { return self::getWHEREclause( $conditions ); }
	/*
	 * getWHEREclause( $Conditions): construct a SQL WHERE clause from a given
	 * list of conditions
	 * @Conditions: comma-separated list of NVPairs
	 * @returns: (string) well-formed SQL WHERE clause of conditions
	 */
	 public static function getWHEREclause( $Conditions = null, $caller = "unknown") {
	 	global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( $container->debug && $container->debug_level > 1)
			echo "$tag->event_format : conditions=$Conditions, caller = $caller<br>\r\n";
		
		// if conditions are null, just return '1'
		if ( ! $Conditions ) {
			if ( $container->debug && $container->debug_level > 1)
				echo "$tag->event_format: there are no conditions so just returning WHERE 1<br>\r\n";
			return ' WHERE 	1 ';
		}

         // save original for any last-minute processing
        $original_conditions = $Conditions;

		$Conditions = explode( ',' ,$Conditions);
		
		$Clause = ' WHERE (';
		
		$FirstOne = true;
		
		// was the last one an or?
		$last_was_or = false;
		
		foreach ( $Conditions as $Condition) {
            if ($container->debug && $container->debug_level >=2 ) echo "$tag->event_format: evaluating Condition $Condition<br>\r\n";
			$matched = false;
		
			// set default splitter
			$splitter = '=';

            // IS NULL
            if ( preg_match ('/\sIS\sNULL/', $Condition ) ){
                if ($container->debug ) echo "$tag->event_format: splitter is IS for $Condition<br>\r\n";
                $splitter = 'IS';
                //continue;
            }
            // if just a 1, continue
			if ( preg_match ('/^[1]{1}$/', $Condition ) )
				continue;
			
			// first case: split a LIKE phrase
			
			
			// group by 
			if ( preg_match( '/GROUP BY/', strtoupper( $Condition ) ) )
				continue;

			// group by 
			if ( preg_match( '/(SORT|ORDER) BY/', strtoupper( $Condition ) ) ){
                if ($container->debug && $container->debug_level >=2 ) echo "$tag->event_format: order by condition must be handled at end of loop!<br>\r\n";
                continue;
            }

			// special case!
			if ( preg_match ( '/LIMIT/' , strtoupper( $Condition ) ) )
				continue;
				
			// special case!
			if ( preg_match ( '/OFFSET/' , strtoupper( $Condition ) ) ){
                if ($container->debug && $container->debug_level >=2 ) echo "$tag->event_format: offset condition must be handled at end of loop!<br>\r\n";
                continue;

            }

			if ( preg_match( '/\s+RLIKE\s+/' , $Condition  ) )
				$splitter = 'RLIKE';
			elseif ( preg_match( '/\s+LIKE\s+/' , $Condition  ) )
				$splitter = 'LIKE';
			if (preg_match('/\s+IN\s+/',$Condition))
                $splitter = 'IN';
				
			elseif ( preg_match( '/!=/' , $Condition ) ) 
				$splitter = '!=';
			elseif ( preg_match( '/<=/' , $Condition ) )
				$splitter = '<=';
			elseif ( preg_match( '/</' , $Condition ) )
				$splitter = '<';
				
			elseif ( preg_match( '/>=/' , $Condition ) )
				$splitter = '>=';
			elseif ( preg_match( '/>/' , $Condition ) )
				$splitter = '>';
			//elseif ( preg_match('/\s+(is|Is|IS|iS)\s+/',$Condition,$hits))
			//	$splitter = $hits[1];
			
				
			
			// split the condition into components
			$NVPair = explode( $splitter, $Condition );
            if ( $container->debug && $container->debug_level >1)
                echo "$tag->event_format: NVPair is ". new xo_array($NVPair). "<br>\r\n";
			// throw an exception if necessary
			global $container;
			if ( count($NVPair)< 2)
				$container->exception( xexception::illegal_argument, 
					new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__),
					"<span style=\"font-family: courier; color: black\">\"$Condition\"</span> cannot be split properly to create a name/value pair.".
					"this is likely because the caller ( $caller ) did not pre-process using the Human Language Module. splitter = $splitter (<span style='color: green'>$Condition " .new xo_array($NVPair)."</style>)");
			
			if ( self::$Debug && $container->debug_level > 1)
				Debugger::echoMessage( get_class() . '::' . __FUNCTION__ . '(): Raw condition=' . $Condition);

			// if not the first component, add AND or OR based on what is needed
			if ( ! $FirstOne ) {
			
				if ( $last_was_or && ! preg_match ('/OR:/' , $NVPair[self::NAME] ))
					$Clause .= " ) ";
				$Clause .= preg_match ('/OR:/' , $NVPair[self::NAME] ) ? ' OR ' : ' AND ';
				if ( $last_was_or && ! preg_match ('/OR:/' , $NVPair[self::NAME] ) )
					$Clause .= " ( ";
				
			}
			
			// now its definitely not the fist one
			$FirstOne = false;
			$name = preg_match ('/OR:/' , $NVPair[self::NAME] ) ?  substr( trim($NVPair[self::NAME]), 3 ) : trim($NVPair[self::NAME]);
			$val = $splitter == 'LIKE' ? trim( $NVPair[self::VALUE]): trim( $NVPair[self::VALUE]) ;
			if ($splitter == 'IN')
                $val = preg_replace('/\./',',',$val);
            if ( $container->debug && $container->debug_level >1) echo "$tag->event_format: name is <span style='color:violet'>$name</span><br>\r\n";
            if ( $container->debug && $container->debug_level >1) echo "$tag->event_format: splitter is <span style='color:violet'>$splitter</span><br>\r\n";
            if ( $container->debug && $container->debug_level >1) echo "$tag->event_format: val is <span style='color:violet'>$val</span><br>\r\n";
            if ( preg_match('/LOWER\(([a-z|0-9|_]+)\)/',$name,$hits)){
                $Clause .= "LOWER(`$hits[1]`) $splitter $val";
            } else {
                $Clause .=  (preg_match('/`/',$name)) ?" $name   $splitter $val": " `$name`   $splitter $val";

            }

			// if the last one was an or
			if ( preg_match ('/OR:/' , $NVPair[self::NAME] ) )
				$last_was_or = true;
			else $last_was_or = false;
		}
		
		// fix
		if ( trim ( $Clause ) == 'WHERE' || trim( $Clause) == 'WHERE (')
			$Clause = ' WHERE 1 ';
		else $Clause .= " )";
		if ( $container->debug && $container->debug_level >1 )
			echo "$tag->event_format :  returned clause = $Clause<br>\r\n"; 

        // last thing, if an or comes after an and, we need more parentheses
         if ( preg_match('/(.+)\s+AND\s+(.+)\s+(.+)\s+(.+)\s+OR\s+(.+)\s+(.+)\s+(.+)/',$Clause,$hits)){
            $Clause = "$hits[1] AND ( $hits[2] $hits[3] $hits[4] OR $hits[5] $hits[6] $hits[7] )";
         }

        if ( preg_match( '/order\s+by\s+([a-z|_]+)\s+(asc|desc)/', strtolower( $original_conditions ),$hits ) ){
           if ($container->debug && $container->debug_level >=2 ) echo "$tag->event_format: order by condition may now be handled<br>\r\n";
             $Clause .= " ORDER BY $hits[1] $hits[2]";
         }

         // if we had an offset include it

         if ( preg_match('/offset ([0-9]+)/',$original_conditions,$hits))
                $Clause .= " LIMIT 10000 OFFSET $hits[1]";

	    if ( $container->debug && $container->debug_level >1
        ) echo "$tag->event_format: returned clause is $Clause<br>\r\n";
		return "$Clause";
	 	
	 }
	 
	 //! convert inline operators
	 public static function convert_inline_ops( $str ) {
	 
		$regex = array( '/\[eq\]/' => '=' ,  '/\[ne\]/' => '!=' , 
			'/\[LIKE\]/' => 'LIKE' );
		
		foreach ( $regex as $reg => $replace )
			$str = preg_replace( $reg, $replace, $str);
			
		return $str;
	 
	 }
	
}

?>