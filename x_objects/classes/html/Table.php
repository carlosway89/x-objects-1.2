<?php 
/*
 * Project:			PlatformPHP
 * Module:			Table
 * Version:			1.0
 * Author:			David Owen Greenberg
 * Descr:			Object representation of an HTML Table
 */
class Table 
	extends MySQLService				// data abstraction layer access 
	implements iDisplayableObject {		// requires Table to be able to display itself as HTML

	/*
	 * The Specification is a recursive array that narrates aspects of the table, including sections, ids
	 * and content.  Here is a list of how the table spec should be structured:
	 * array 	(
	 * 				'name' => 'Specify a Name for Table, used in HTML class= tags'
	 * 				'id' =>	'Specify an HTML id for table element'
	 * 				'datasource' => 'Specify the table where the fields will be retrieved'
	 * 				'headers' => array ( 'table_column' => 'display_value' , ... )
	 * 				
	 * 			)
	 */
	private $Spec = null;
	
	// holds the table data
	private $TableData = null;
	
	function __construct( $Specification ) {
	
		// connect to data layer
		parent::__construct();
	
		// save the specification
		$this->Spec = $Specification;
		
		// if a datasource is provided, attempt to open it
		if ( isset($this->Spec['datasource'])) {

			// fetch all from the database
			try { $this->TableData = $this->FetchAllFrom( $this->Spec['datasource']); }
			catch (Exception $e) 
			{ throw $e; }
		
		}
		
	}
	
	/*
	 * displayAsHTML(): Output the formatted contents of the table as HTML with tags 
	 * (required by interface)
	 */
	public function displayAsHTML() {
	
		// print out the table header with supplied tags	
		echo '<table class="' . $this->Spec['name'] . '" name="' . $this->Spec['name'] . '" id="' . $this->Spec['id']  . '">' . "\r\n";
		
		// we need the fields in order to display the table header
		$Fields = $this->getFields( $this->Spec['datasource']);
		
		// now create the table header
		echo '<tr class="TablePHP">' . "\r\n";
		foreach ( $Fields as $Field) {
			// print this header only if it's specified by the user when creating the Table
			if ( isset($this->Spec['headers'][$Field->name]) )
				echo '<td class="' . $this->Spec['name']  . '">' .  $this->Spec['headers'][$Field->name] . '</td>' . "\r\n";
		}
		echo '</tr>' . "\r\n";
		
		// now display data for each row
		foreach ( $this->TableData as $Object) {
			echo '<tr class="' . $this->Spec['name'] . '">' . "\r\n";
			foreach ( $Fields as $Field) {
				if ( isset ( $this->Spec['headers'][$Field->name])) {
					echo '<td class="' . $this->Spec['name'] .'">'; 
					if ( FormFactory::isURL( $Field ))
						echo '<a href="' . $Object->get( $Field->name ) . '">';
					echo $Object->get( $Field->name ); 
					if ( FormFactory::isURL( $Field ))
						echo '</a>' . "\r\n";
				}
				
			}
			echo '</td></tr>' . "\r\n";
			
		}
		//echo '</tr>' . "\r\n";
		echo '</table>' . "\r\n";
	}

}
?>