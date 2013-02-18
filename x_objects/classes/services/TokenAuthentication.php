<?php

class TokenAuthentication {

	// validate a token
	public static function valid( $token , $type ) {
	
		switch ( $type ) {
		
			case 'importer' :
			
				if ( $result = MySQLService2::query( 'select token from data_importer' ) ) {
				
					$row = $result->fetch_assoc();
				
					$dbToken = $row['token'];
				
					$result->close();
				
					return $token === $dbToken; 
					
				} else return false;
			
			break;
		
		}
	
	}

}

?>