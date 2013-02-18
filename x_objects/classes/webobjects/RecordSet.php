<?php
//! a RecordSet is a set of objects/records from the database, that can be retrieved and displayed 
class RecordSet {

	//! debugging
	private $debug = false;

	//! the records
	private $records = null;
	
	//! the view
	private $view = null;
	
	//! view for no records
	private $none_view = null;
	
	//! key for recordset
	private $key = null;
	
	//! group view
	private $group_view = null;
	
	//! construct with key, query and view
	public function __construct( $key, $query, $view, $none_view, $group_view = null,$wrapper=true ) {
		global $container;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// create a hash for the recordset
        $this->hash = $container->services->utilities->random_password(5);
		if ( Debugger::enabled() )
			$this->debug = true;
	
		// save view
		$this->view = $view;
		$this->none_view = $none_view;
		$this->key = $key;
		$this->group_view = $group_view;
		$this->query = $query;
		$this->wrapper = $wrapper;
		
		if ( $this->debug )
			Debugger::echoMessage( "Recordset (key,query,view,group_view,none_view) = ($key,$query,$view,$group_view,$none_view)");
		
		// load the records
		if ( $this->key ) {
		
			// manufacture the records
			try {
				$key = x_objects::instance()->key_to_class( $key );
				// we need an example object for the id field
                if ( ! class_exists($key)){
                    throw new Exception("<span style='color:green;font-weight:bold;'>$key</span> is not a valid class.  Make sure you have created the appropriate business object class in /app/models");
                }
				$obj = new $key();
				$id = $obj->source()->keycol();
				if ( $container->debug)
					echo "$tag->event_format : key = $key, idcol = $id<br>\r\n";
				$this->records = ObjectFactory::create( $key, null, null, null, null, 
					HumanLanguageQuery::create( $query, $id, get_class()."::".__FUNCTION__ )->conditions() );
					
				//if ( $key <> "xevent")
					//$container->log( xevent::debug, "$tag->event_format : manufactured " . count( $this->records ). " records");	
			} catch ( Exception $e ) {
				$container->exception( xexception::exception, $tag, ": ". $e->getMessage() );
			}
			// optional usort
			@usort( $this->records , x_objects::instance()->key_to_class( $key ) . "::usort");
			
			// check vcache pointer status and update if necessary
			if ( ObjectFactory::$using_vcache_pointer && ObjectFactory::$vcache_pointer_status == 'stale' ) {
			
				vcache::create( $query )->reset_day_one();
		
			}
			
		} else {
		
			$this->records = array();
		
			if ( Debugger::enabled() )
				echo get_class() . ": key is null, not manufacturing anything...<br>";
		
		}
	
	}
	
	//! return recordset as xHTML
	public function xhtml( $wrapper = false ) {
	
		// optional: save last grouping value
		if ( preg_match( '/([a-z]+)\-([a-z]+)\-view/' , @$this->group_view , $matches ) ) {
			$grouping = $matches[2];
			$last_group = '';
		}
	
		$html = "";
		
		if ( $wrapper )
			$html .= "\t" .'<div class="recordset-wrapper">' . "\r\n";
			
			
		if ( $this->records && count( $this->records )>0) { 
			foreach ( $this->records as $record ) {

            // apply hash and other values
            $record->recordset_hash = $this->hash;
            $record->recordset_query = $this->query;

			// handle optional grouping
			if ( @$grouping ) {
				if ( $last_group != $record->$grouping ) {
					
					$last_group = $record->$grouping;
					if ( $this->group_view ) {
						$html .= x_object::create( $this->group_view )->xhtml( $record );
					}
				}
			}
			
		
			// check for xml string instead of a file load
			$view = $this->view;
			global $$view;
			if ( @$$view ) {
				$html .= $record->html( simplexml_load_string( $$view ));
			}
			else {
				$html .= $record->html( $this->view );
			}
		}
		} else {
			if ( ! count( $this->records ) )
			if ( ! $this->none_view )
				throw new ObjectNotInitializedException( get_class() . " $this->key : You must specify a none_view in the XML. ($this->none_view)");
			else {
				$view = $this->none_view;
				global $$view;
				$xml = (@$$view)?simplexml_load_string($$view):$this->none_view;
				$xobj = (is_object($xml))? new x_object(null,null,$xml) : x_object::create( $xml );
				$dummy = new $this->key();
                $dummy->recordset_query = $this->query;
                $html .= $xobj->xhtml($dummy);
			}
		}
		
		// optional add paginator
		
		if ( $this->key && preg_match( '/from page ([0-9]+)/' , $this->query, $matches)) {
			$page = $matches[1];
			$obj = new $this->key();
			$paginator = $obj->paginator( $page );
		}
		
		if ( @$paginator )
			$html .= $paginator->xhtml;
		//else $html .= "\t\t".'<div class="no-paginator"></div>' . "\r\n";
		
		if ( $wrapper )
			$html .= "\t".'</div><!-- end wrapper -->' ."\r\n";
			
		return $html;
	}
	
	//! create a new RecordSet
	public static function create( $key, $query, $view, $none_view, $group_view = null , $wrapper=true) {
	
		return new RecordSet( $key, $query, $view , $none_view, $group_view,$wrapper );
		
	}
	
	//! is the record set empty?
	public function is_empty() {
		
		return ( ! count( $this->records ));
		
	}
	


}


?>