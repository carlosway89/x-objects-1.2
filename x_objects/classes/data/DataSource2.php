<?php 

//! 2nd generation datasource class, streamlined for easier use
class DataSource2 extends AttributedObject {

	// source types
	const TABLE = 1;
	const DERIVED_TABLE = 2;
	const VIEW = 3;
	
	// datasource name
	private $name;
	
	private $xml;
	
	//! the columns
	private $columns = array();
	
	// key column
	private $key;
	
	// type
	private $type = self::TABLE;
	
	// alias, knickname for derived tables
	private $alias;
	
	// read only columns
	private $readOnly = array();

    // type name translation
    private static $types = array(
        1 => "boolean",
        3 => "int",
        12 =>"datetime",
        7 =>"timestamp",
        253 =>"varchar",
        252 =>"text"
    );

    // no import columns
    private $no_import = array();
    // import columns
    private $import_cols = array();
	/*! construct a new DataSource with the given attributes
	\param @SourceName the name of the data source, such as table or view name
	*/
	function __construct( $xml ) {
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		// first argument must be an object of type SimpleXMLElement
		if ( ! is_object( $xml ) )
			throw new IllegalArgumentException( "DataSource2::__construct( $xml ): First argument must be an object." );
			
		// save the xml
		$this->xml = $xml;
		
		// save values to object
		$this->name = $this->table_name = (string) $xml->name;
		$this->type = (string) $xml->type;
		$this->alias = (string) $xml->alias;
		$this->key = (string) $xml->key;
		$this->readOnly = explode( ',' , $xml->read_only );
        $this->no_import = $xml->noimport? explode(',',$xml->noimport):array();
        $this->import_cols = $xml->import? explode(',',$xml->import):array();

        // load the columns
		$query = " SELECT * FROM `$this->name` WHERE 1 LIMIT 1;";
		if (  $result = MySQLService2::query ( $query, get_class()." ".__FUNCTION__ ) ) {
			$fields = $result->fetch_fields();
			foreach ( $fields as $field )
				array_push( $this->columns , $field->name );
			if ( $container->debug && $container->debug_level > 2) { 
				echo "$tag->event_format: db fields appear below<br>\r\n";
				print_r( $this->columns);
				echo "<br>\r\n";
			}
		} else {
			//throw new DatabaseException("$tag->exception_format: ".MySQLService2::getSQLError());
		}
		
		
	}
	
	// magic set
	public function __set ( $what, $val ) {
	
		$this->set( $what, $val );
	}
	
	// magic get
	public function __get( $what ) {
	
		switch ( $what ) {
			// get auto_datetime
			case 'auto_datetime':
				return $this->xml->auto_datetime;
			break;
			// get fields
			case 'fields':
				return $this->fields();
			break;
			// get the source name
			case 'name': return $this->name(); break;		
			// get the id column
			case 'id_column': return $this->keycol(); break;
		
			// get the csv columns
			case 'csv_columns':
			
				return $this->xml->csv_columns;
			
			break;
		
			// get columns as a string
			case 'columns_str':
			
			
				return implode( $this->columns() , ',');
				
			break;
		
		
			// get the destination for saves
			case 'destination':
			
				return (string) $this->xml->destination ? (string) $this->xml->destination : (string) $this->xml->name;
				
			break;

			case 'write_keycol':
			
				return $this->write_col_for( $this->keycol() );
				
			default:
			
				return $this->get( $what );
				
			break;
			
		}
	
	}
	
	//! get the write column for a given value
	public function write_col_for( $col ) {
	
		if ( (string) $this->xml->write_name )
			return (string) $this->xml->write_name->$col;
		else
			return $col;
	}
	
	/*
	 * returns name of source, which may also be a subquery or view.
	 * Always matches and safe for db query
	 */
	public function name() { return $this->name; }
	
	// returns alias, used to mask derived tables as DataObjects
	public function alias() { return $this->alias; }
	
	// returns managed columns
	public function columns() { return $this->columns; }
	
	
	// returns key column name
	public function keycol() { return $this->key; }
	
	//! returns the fields details for this data source
	public function fields() { return MySQLService2::getFields( $this->name()); }
	
	//! not implemented yet
	public function isReadOnly( $key ) { return false; }
	
	//! returns the number of columns
	public function numcols() { return count( $this->columns ); }
	
	//! is this a read-only column?
	public function is_read_only( $name ) {
	
		// not implemented yet
		return in_array( $name, $this->readOnly );
	}

    /**
     * @param $name col name
     * @return bool true if excluded from import
     */
    public function no_import( $name ) {

        // not implemented yet
        return in_array( $name, $this->no_import);
    }



    // is a field required?
	public function required( $name){
		return in_array( $name, explode( ',', (string)$this->xml->required));
	}
    // translate field types (numeric) into human word
    public function translate_type($id){

        return isset( self::$types[$id])?self::$types[$id]:$id;
    }

    /**
     * get display name for a column name
     */
    public function display_name_for( $name){
        return ucfirst(preg_replace('/_/',' ',$name));
    }
    public function import_columns(){
        return $this->import_cols;
    }
}


?>