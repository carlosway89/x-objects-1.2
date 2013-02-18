<?php

class filesystem {

	private static $debug = false;

	//! delete a file
	public static function delete( $args ) {
	
		$myargs = new args( $args );
		
		return unlink( $myargs->filename ) ? "success unlink $myargs->filename" : "error unlink $myargs->filename";
	}

	//! rename a file
	public static function rename( $args ) {
	
		$myargs = new args( $args );
		
		
		// rename based on rules
		switch ( $myargs->type ) {
		
			
	
			case 'canonical':
			
				$newname = date( 'Ymd' , time() ) . '_' . Utility::createRandomPassword( 10 );
				
				// get existing directory
				$one_slash = '\/{1}';
				$folder = '.+' . $one_slash;
				$filename = '.+\.{1}';
				$ext = '[a-z|A-Z|0-9]+'; 
				
				$regex = '/^(' . $one_slash . '(' . $folder . ')+)(' . $filename . '(' . $ext . '))$/';
				
				if ( self::$debug )
					echo "$regex $myargs->name <br>";
				
				if ( preg_match( $regex , $myargs->name , $matches)) {
					
					
					if ( self::$debug )
						print_r( $matches);
					$dirs = $matches[1];
					$filename = $matches[ count( $matches) -2];
					$ext = $matches[count($matches)-1];
					
					$newfullname = $matches[1] . $newname . "." . $ext;
						//echo "rename $matches[0] to $newfullname<br>";
						
					// wait a few times for the file
					for ( $i = 0 ; $i < 20;					$i++ )
						if ( file_exists( $matches[0] ))
							break;
						else {
							//echo "not found";
							usleep( 500000 );
						}

/*						
					$cmd = "mv $matches[0] $newfullname";
					exec( $cmd, $out, $retval );
					
					return ( ! $retval ) ? "success $newfullname" : "error $out";
	*/				
					return rename( $matches[0] , $newfullname ) ? "success $newfullname" : 'error';
					
				} else {
				
					if ( self::$debug )
						echo "error no matched file from regex<br>";
						
				}
				
			break;
	
		}
		
	
	}

}