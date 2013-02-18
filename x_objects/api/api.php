<?php
/**
 * This is the REST API engine for x-objects.
 * 
 * Here are some example invocations, that demonstrate the different modules
 *
 * Example 1: Invoke a service 
 * http://www.mysite.com/api/service/my_service/my_method
 * ( the invoked class must implement x_service interface)
 */
 /**
  * sessions are required since many functions must be performed when user is logged in.
  */
@session_start();

/**
 * bootstrap x_objects in order to get access to the application container
 */

//require_once('../include/bootstrap.php');
// get preloader
global $pathroot;

$container = x_objects::instance();
$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"","");

if ( $container->debug)
	echo "<===== API MODULE DEBUGGING START =====><br>\r\n";

// set logged in class
$logged_in_class = $container->logged_in() ? 'logged-in' : 'not-logged-in';


if ( file_exists( $pathroot . "api/api.preloader.php") ) {
	require_once( $pathroot . "api/api.preloader.php");
	
}

// load views
if ( file_exists( $pathroot . "views/global.php") ) {
	require_once( $pathroot . "views/global.php");
}

if ( file_exists( $pathroot . "views/api.php") ) {
	require_once( $pathroot . "views/api.php");
}


// set debugging
$debug = (Debugger::enabled()) ? true : false;

// create a tag
$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,"none","none");

// get REQUEST
$req = new REQUEST();
$uri = new REQUEST_URI();

	
// set module 
$module = $req->module? $req->module : $uri->part(2); 
$method = $req->method? $req->method : $uri->part(4);
$key = $req->key ? $req->key : $uri->part(3);
$query = $req->query ? $req->query : $uri->part(4);
$view = $req->view ? $req->view: $uri->part(5);
$none_view = $req->none_view ? $req->none_view : $uri->part(6);

// for report wizard need to save cols as a global
$report_wizard_columns = '';

if ( $debug ) 
	echo "$tag->event_format : module $module method $req->method args $req->args key $key id $req->id query $query<br>\r\n";
switch ( $module ) {
    case 'report_wizard':
        $report_wizard_columns = $req->cols;
        ///echo $req->time_constraint;
        echo new xo_report_wizard_result($key,$query,$view,$none_view,$req->cols,$req->time_constraint);
    break;
	// instant lookup
	case 'instant_lookup':
		$c = $uri->part(5);
		echo new instant_lookup($key,$query,$req->val,$c);	
	break;
	// the twitter module allows interaction with the twitter API
	case 'twitter':
		switch( $req->method){
			case 'tweet':
				$container->apis->twitter->tweet($req->tweet);
			default:
				echo "unknown method $req->method";
			break;
		}
	break;
	// the service module allows you to access a named web service directly
	case 'service':
		$key = $req->key;
		$method = $req->method;
		try {
			echo $container->services->$key->$method( $req->json );
		} catch ( Exception $e ){
			echo $e->getMessage();
		}
	break;
	// basic network authentication
	case 'auth':
	
		try { 
		
			if ( $req->method == 'logout')
				echo auth::logout();
		
			else echo auth::execute( $req->key, $req->username, $req->password );
		
		} catch ( Exception $e ) {
		
			echo "error " . $e->getMessage();
			
		}
	
	break;

	// search
	case 'search':
	
		try {
	
			echo RealSearch::create( 
				$req->key, 
				$req->query, 
				$req->view, 
				'RLIKE', 
				$req->filters,
				$req->page,
				$req->size
			)->execute()->xhtml( true);
		} catch ( Exception $e ) {
		
			echo "error " . $e->getMessage();
		}
	break;

	// filesystem module
	case 'filesystem':
	
		
		
		
		echo filesystem::$method( $args );
		
	break;

	// factory, used to create objects
	case 'factory':
		$args = ( $req->method =='fetch')?$req->query:$req->args;	
		call_user_func( "factory::$req->method" , $req->key, $args, $req->view); 
		
	break;

	// recordset module
	case 'recordset':
	
		$wrapper = ( $req->wrapper == 'no')?false:true; 
	
		try {
		
			echo RecordSet::create( $key, $query, $view, $none_view)->xhtml( $wrapper);
	
		} catch ( Exception $e ) {
		
			echo "error exception " . $e->getMessage();
		
		}
	
		
	break;
	
	case 'business':
		// check if id has a colon
		if ( preg_match( '/:/', $req->id ) ) {
		
			$raw = explode( ':', $req->id );
			$field = $raw[0];
			$val = $raw[1];
			
			$search = "$field='$val'";
		} else {
			if ( $req->id ){ 
				$obj = new $req->key();
				$idfield = $obj->source()->keycol();
				$search = "$idfield='$req->id'";
			}
			else $search = "";
		}
		
		try {
			$object = new $key( $search );
		} catch ( Exception $e) {
			echo $e->getMessage();
		}
//		$method = $req->method;
		
		switch ( $method ) {
			// empty a table of all elements
			case 'empty':
				echo call_user_func("$key::truncate");
			break;
			case 'new':
			case 'create':
				// get format
				$format = ($req->format)? $req->format: "json";
				// get view
				$view = $req->view;
				// get JSON
				$json = ($req->JSON)? $req->JSON : $req->json;
				if ( $json) {
					// special handling for xevents
					if ( $req->key == 'xevent') {
						// parse json
						$parms = json_decode( $req->json );
						// get the tag
						$tag = xo_codetag::from_json( $parms->tag );
						// set msg
						//$object->event_type_id = $parms->event_type_id;
						//$object->message = "$tag->event_format : $parms->message";
						$container->log( $parms->event_type_id, "$tag->event_format : $parms->message");
					} else {
						// if applicable, use auto_datetime to set a datetime field
						$auto_dt = (string)$object->source()->auto_datetime;
						if ( $auto_dt ){
							if ( $container->debug)
								echo "$tag->event_format : setting auto_datetime for $auto_dt";
							$object->$auto_dt = date('Y-m-d H:i:s');
							//echo $object->$auto_dt;
						}
						if ( $container->debug )
								echo "$tag->event_format : setting from json<br>\r\n";
						if ( $object->set_from_json( $json) === false) {
							header('Content-type: application/json');
							echo '{ "error" : "'.$object->error.'"  }' ;
							break;
						}
					}
											
					if ( $object->save()) {
						if ( $req->key != "xevent" )
							$container->log( xevent::success ,
							"$tag->event_format : successfully created a new BusinessObject of type $req->key id $object->id");
						// get appropriate format
						try { 
							// refresh database fields
							$idfield = $object->source()->keycol();
							//echo " $idfield " .$object->$idfield;
							$object->load( (int)$object->$idfield );
							//echo "now " .$object->$idfield;
							global $$view;
							if ( is_string( @$$view) && ! @$$view == '')
								$view = simplexml_load_string( $$view);
							if ( $format =='json'){ 
								header('Content-type: application/json');
								echo $object->json;
							} else echo $object->html($view);
						} catch ( Exception $e) {
							echo $e->getMessage();
						}
					}
					else {
						
						header('Content-type: application/json');
						echo '{ "error" : "'. $object->save_error .'" }' ;
					}
				} else echo "error api business $req->key $req->method: expecting JSON for object values, not found in REQUEST";
	
			break;
		
			case 'view':
			
				echo $object->$method( $req->view );
				
			break;
			case 'json':
				header('Content-type: application/json');
				echo ($object->exists) ? $object->$method($req->args) : "{}";
			break;
			default:
				try {
					echo $object->$method( $req->args );
				} catch ( Exception $e) {
					echo $e->getMessage();
				}
			break;
			
		}
	break;

	// mysql service module
	case 'mysql_service':
	
		echo call_user_func( "$module::$method" , $args );
	
	break;

	// no action defined
	default:
	
		echo "error unknown module $req->module";
		
	break;

}

if ( $container->debug)
	echo "<===== API MODULE DEBUGGING END =====><br>\r\n";


?>