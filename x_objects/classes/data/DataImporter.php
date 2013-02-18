<?php

class DataImporter {

	private $config = null;
	
	// set debugging
	private $debug = false;
	
	// construct a new one
	public function __construct( $token, $options = null ) {
	
		// set debugging
		if ( Debugger::enabled() )
			$this->debug = true;
	
		$this->token = $token;
		
		$this->config = new RealXML( 'import-config' );
		
		$this->options = $options;
		
	}
	
	// magic get/set
	public function __set ( $what, $val ){
	
		$this->$what = $val;
	
	}
	
	public function __get ( $what ) {
	
		switch ( $what ) {
		
			case 'show_sql':
				return isset( $this->options['show_sql'] ) && $this->options['show_sql']; 
			break;
			
			default:
				return $this->$what;
			break;
		}
		
	}
	
	// display as well-formed xhtml
	public function xhtml() {
	
		$html = '';
		
		// check token auth
		if ( ! TokenAuthentication::valid( $this->token , 'importer' ) ) {
			$html .= div::create( 'class=result-row result-error','Unrecognized or invalid authentication token')->html();
			return $html;
		}
		else {	
			$html .= div::create('class=result-row result-success' ,'Authentication successful')->html();
		}
		
		// process uploaded files
		$html .= $this->process_files();
		
		return $html;
		
	}
	
	//! process a specific file
	public function process_file( $path ) {
	
		$html = '';
		
		$handle = fopen( $path, "r");
		
		// get the header line
		$header = new DataImportHeader( $this->config->xml(), fgets( $handle ) );
		
		$count = 0;
				
		while ( ! feof( $handle ) ) {
			$count++;
			$row = DataImportRow::create( $header->key, fgets( $handle ), $header );
			if ( $this->show_sql )
				$html .= div::create( 'class=result-row result-info', "row $count sql: " . $row->sql )->html(); 
				
			if ( $row->import() )  
				$html .= div::create( 'class=result-row result-success', "successfully imported row $count" )->html();
			
			else 
				$html .= div::create( 'class=result-row result-error', "failed to import row $count: $row->save_error" )->html();
				
			if ( $row->header_mismatch_error )
				$html .= div::create( 'class=result-row result-error', "row $count:" . $row->header_mismatch_error )->html();
				
		}
		
		fclose( $handle );
		
		return $html;
	}
	
	//! process files
	public function process_files() {
	
		if ( $this->debug )
			echo get_class() . " processing files...<br>";

			$html = '';
		
		// open this directory 
		global $directory_name;
		
		$dirname = PATHROOT . "$directory_name/importer/files/";
		$dir = opendir( $dirname );

		// get each entry
		while($file = readdir($dir)) {
			if ( ! preg_match( '/^\./' , $file ) ) {
				$html .= div::create('class=result-row result-success',"Processing file: $file")->html();
				$html .= $this->process_file( $dirname . $file );
				if ( unlink( $dirname . 	$file ) )
					$html .= div::create('class=result-row result-success',"deleted file: $file")->html();
				else
					$html .= div::create('class=result-row result-error',"failed to delete file: $file")->html();
				
					
			}
		}

		// close directory
		closedir($dir);

		return $html;
	
	}
	
	// get a data importer
	public static function get( $token , $options = null) {
	
		return new DataImporter( $token , $options );
		
	}

}

?>