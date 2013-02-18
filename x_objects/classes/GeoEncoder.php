<?php 
/*
 * Project:			platformPHP
 * Module:			GeoEncoder
 * Version:			1.1
 * ModDate:			June 11, 2010
 * Author:			David Owen Greenberg
 * Descr:			Used to encode addresses into geo coordinates
 */
class GeoEncoder {

	const URL_BASE = "http://maps.google.com/maps/geo?q=";

	const FORMAT_NESW = 1;
	const FORMAT_PLUSMINUS = 2;
	
	// we need the Google MAPS API Key to decode
	private $GoogleAPIKey = null;
	
	// member for the status code
	private $StatusCode = 0;
	
	// address format
	private $AddressFormat;
	
	// precision for coordinates
	private $Precision = 2;

	function __construct( 
		$GoogleAPIKey = null,						// specify a google API key, required
		$AddressFormat = self::FORMAT_NESW,			// what format should the output address be?
		$Precision = 2								// how many decimal places of precision to use
	) {
	
		$this->GoogleAPIKey = $GoogleAPIKey;
		$this->AddressFormat = $AddressFormat;
		$this->Precision = $Precision;
	
	}
	
	/*
	 * convertToGeo( $Address ): converts a physical address to geo encoding
	 */
	public function convertToGeo( $Address ) {
	
		// encode the address as a URL
		$URLAddress = urlencode( $Address );
		
		// set the URL for encoding via the API
		$URL = self::URL_BASE . urlencode( $URLAddress)  . "&output=xml&key=" . $this->GoogleAPIKey;
		
		// debugging only
		//echo file_get_contents( $URL );
	
		// parse the address and load the response as an XML file
		$XML = simplexml_load_file( $URL );
		
		// check the status response back from the API
		$this->StatusCode = $XML->Response->Status->code;
		
		/*
		 * tokenize address and transform to E/W format
	     */
		if ( $this->AddressFormat == self::FORMAT_NESW) {
			$Lat = strtok( $XML->Response->Placemark->Point->coordinates , ',');
			if ( substr( $Lat, 0, 1 ) == '-')
				$Lat = 'W' . substr( $Lat, 1);
			else $Lat = 'E' . $Lat;
			$Log = strtok( ',');
			if ( substr( $Log, 0,1) == '-')
				$Log = 'S' . substr( $Log, 1);
			else $Log = 'N' . $Log;
			$Alt = strtok( ',');
			$XML->Response->Placemark->Point->coordinates = $this->setPrecision ( $Log ) . ',' . $this->setPrecision( $Lat ) . ',' . $Alt;
		
		}
			
		
		
		if ( $this->StatusCode == '200')  //address geocoded correct, show results
			return $XML;
		else return null;
		
	}

	/*
	 * getStatusCode(): returns last set status code
	 */
	public function getStatusCode() { return $this->StatusCode; }
	
	/*
	 * setPrecision(): sets the precision for latitude and longitude
	 */
	private function setPrecision( $Coordinate ) {
	
		if ( DEBUG )
			echo '(DEBUG) : GeoEncoder::getStatusCode(): $this->Precision=' . $this->Precision;
		$BeforeDecimal = strtok ( $Coordinate, '.');
		$AfterDecimal = strtok ( '.' );
		
		if ( $this->Precision == 0)
			return $BeforeDecimal;
		else {
			$ModifiedCoord = $BeforeDecimal . '.' . substr( $AfterDecimal , 0, $this->Precision );
			return $ModifiedCoord;
		}
		
	}

}
?>