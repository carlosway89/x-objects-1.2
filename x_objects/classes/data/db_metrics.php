<?php
//! database metrics
final class db_metrics  {
	
	//! for debugging class methods
	private static $Debug = true;

	/*!constants used to define specific metrics, which can be invoked
	 * by the user in the below methods
	 */
	 const M_TOTAL = 'total';  //! obtain the total for a given source and column
	 const M_SUM = 'sum';	 //! obtain the sum of a specific field
	 const M_MAX = 'max';	//! get max value for a field
	 const average = 'avg';
	 
	 //! translation of metrics into SQL functions
	 private static $Functions = array ( 
		self::M_TOTAL => 'COUNT',
		self::M_SUM => 'SUM',
		self::M_MAX => 'MAX',
		self::average => 'SUM' );
	 
	/*
	 * getMetric( $Name, $Sources, $Conditions): obtain database metric $Name
	 * from specified data sources under specific conditions
	 * @Name: the metric name, a user-friendly version
	 * @Sources: comma-delimited list of data sources, usually database tables and/or views
	 * @Conditions: specific conditions, usually formed as part of WHERE clause
	 * @returns: the result of the metric query
	 */
	 static public function metric ( $Name , $Sources , $Conditions = null, $fieldname = null) {
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
	
		// get DB Pool
		$DBPool = DatabasePool::getSingleton();
		$SQL = $DBPool->getConnection();		
	
		// split sources into discreet members 	
		$Sources = explode( ',', $Sources);
		
		// store metric results
		$Metric = 0;
		
		// set the target column(s)
		$target = $fieldname ? $fieldname : '*';
		
		// iterate through sources 
		foreach ( $Sources as $Source) {
			
		
			// form SQL
			$Query = 'SELECT ' . self::$Functions[$Name] . '( ' . $target . ' ) `' . $Name . '` FROM ' . $Source  . SQLCreator::getWHEREClause( $Conditions);	

			if ( Debugger::enabled()) {
				Debugger::echoMessage( 'DatabaseMetrics::getMetric(): query=' . $Query);
			}
				
			// run query
			if ( $Result = $SQL->query( $Query) ) {
				
				if ( Debugger::enabled() ) 
					Debugger::echoMessage( 'DatabaseMetrics::getMetric(): query successful');
			
				// obtain results
				$Row = $Result->fetch_assoc();		
				
				// add to metric
				$Metric += $Row[$Name];
				
			} else {
				$container->warn( "$tag->event_format : the metric query failed ( $Query ) because $SQL->error");
			}
			
		} 	
		
		// return metric
		//$denom = db_metrics::metric("total",implode(',',$Sources),$Conditions);
		$avg = (@$denom)? $Metric/@$denom : 0;
		return ($Name == 'avg')? $avg:$Metric;
	 }
	 
	 /*
	  * getCustomMetric( $SQL ): returns the value of a custom metric query
	  */
	public static function getCustomMetric( $Query ) {
		
		// get DB Pool
		$DBPool = DatabasePool::getSingleton();
		$SQL = $DBPool->getConnection();		
	
		// store metric results
		$Metric = 0;
		
		if ( $Result = $SQL->query( $Query )) {
			
			$Row = $Result->fetch_row();
			
			$Metric = $Row[0];
		
			$Result->close();	
		}
	
		return $Metric;	
	}
}

?>