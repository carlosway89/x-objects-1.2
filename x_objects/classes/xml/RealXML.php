<?php

//! representation of an xml document, optionally retrieved from a file

class RealXML {
	
	/**
	 * @property string test descr
	 */

	//! toggle debugging for this class
	private $debug = false;
	
	//! the SimpleXML document
	protected $xml = null;
	
	private static $paths;
	
	//! ie7 compatibility
	public static $ie7_compatible = '';
	
	//! css compatibility mode
	public static $css_compatible = '';
	
	//! reference the container
	private $container = null;
	
	//! construct with an optional file
	public function __construct( $obj_or_src = null ) {
		// set up logging and debugging
		global $container, $webapp_location;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

		if ( Debugger::enabled() )
			$this->debug = true;
	
		global $directory_name;
		
		self::$paths = array( 
			"." ,
			@$webapp_location . "/app/xml", 
			@$webapp_location . "/app/views", 
			@$webapp_location . "/app/views/pages", 
			@$webapp_location . "/app/xml/pages", 
			
			"",
			PATHROOT . 'xml' , 
			PATHROOT . $directory_name . '/xml',
			"./xml" , 
			"../xml" 
			);
			
//			print_r( self::$paths);
		
		//echo (is_object( $obj_or_src))?"yes":$obj_or_src;
		// if we got an object, just save it
		if ( is_object( $obj_or_src )  ) {
			$this->xml = $obj_or_src;
			
		}
		else {
			// normalize the string with an ending, if not present
			if ( ! preg_match ( "/(.)*\.xml/" , $obj_or_src ) && ! preg_match("/(.)*\.xhtml/" , $obj_or_src  ) )
				$obj_or_src .= '.xml';
				
		
			// default no source found
			$src = null;
			
			// walk the paths looking for it
			foreach ( self::$paths as $path ) {
				//echo $path;
				if ( file_exists( "$path/$obj_or_src" ) ) {
					$src = "$path/$obj_or_src";
					break;
				}
			}
			
			if ( $src )
				$this->xml = simplexml_load_file( $src );
			else {
				if ( $container->debug ) 
						echo "$tag->event_format: src is null for $obj_or_src<br>\r\n";
				throw new ObjectNotInitializedException( "realxml exception __construct( $obj_or_src ) : file cannot be found." );
			}
		}
	}
	
	//! get an attribute of the root
	public function attr( $name ) {
		return (string) $this->xml[ $name ];
	}
	
	//! returns all the children, converting to RealXML in the process
	public function children() {
	
		// make a new array
		$children = array();
		
		// get children as simplexmlelements
		
		foreach ( $this->xml->children() as $name => $value )
			array_push( $children , new RealXML( $value ) );
			
		return $children;
	}
	
	//! returns object as HTML
	public function html( 
		$obj = null, 			// optionall pass an object along to bind to fields
		$attributes = null 		// optionally pass attributes
	) {
		global $container,$xobjects_location,$directory_name;
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		
		if ( $container->debug)
			echo "$tag->event_format : begin generating xhtml for $obj<br>\r\n";	
			
		//if ( get_class( $obj) != 'xevent')
			//$container->log( xevent::debug, "$tag->event_format: generating html for ". get_class($obj));
		
		// switch on name of element, for specific handlers
		switch ( strtolower( $this->xml->getName() ) ) {

            // x-enum-select convenient way to select enum values from db column

            case 'x-enum-select':
                $html = new x_enum_select(
                    (string)$this->xml['key'],
                    xQuery::parse((string)$this->xml['default'],$obj,$attributes),
                    xQuery::parse((string)$this->xml['class'],$obj,$attributes)
                );
            break;
			// an x-for
            /**
             * ok this can be a bit complicated, so let me simplify it for you like this.
             * so basically in your loop you can specify a value that can be evaluatd
             * and accessed in your x-object.  For example if you specify value="{counter}"
             * then this means that the value for each step in the loop is the current
             * counter value in the loop 1, 2, 3 etc
             * Then in your x-object you can access it as [get:evaluated]
             */
            case 'x-for':
                if ( $container->debug)
                    echo "$tag->event_format : identified x-for element<br>\r\n";

                $html = for_loop::create( (int) $this->xml['start'],  (int)xQuery::parse((string)$this->xml['end'],$obj,$attributes),(int)$this->xml['incr'],
				(string)$this->xml['view'], (string)$this->xml['value'])->xhtml;
			break;
		
			// a collection of objects
			case 'x-collection':
				
				
				$html = '';
				$key = xQuery::parse((string)$this->xml['key'],$obj,$attributes);
				$view = xQuery::parse((string)$this->xml['view'],$obj,$attributes);
				$suffix= xQuery::parse((string)@$this->xml['view_suffix']);
				$none_view = xQuery::parse((string)$this->xml['none-view'],$obj,$attributes);
				if ( $this->debug)
					echo "$tag->event_format : rendering <span style='color:blue'>x-collection</span> $key $view $suffix<br>\r\n";
				global $$key;
				global $$view;
				global $$none_view;
				if ( is_array( $$key)) { 
					$count = count( $$key);
					if ( $this->debug)
						echo "$tag->event_format : rendering $count objects<br>\r\n";
					
					/*
					 * this was missing before... we need to account for when there aren't any objects
					 */
					if ( ! $count ) {
						$the_view = (@$$none_view)?simplexml_load_string($$none_view):$none_view;	
						$html .= x_object::create($the_view )->xhtml(null,false);
					}
						
					
					foreach ( $$key as $object) {
						//$container->log( xevent::debug, "$tag->event_format : about to render for a new object id=". $object->ID);
						if ( preg_match('/\{([a-zA-Z_]+)\}/',$suffix,$matches))
							$suffix = $object->$matches[1];
						$viewselect = "$view".$suffix;
						global $$viewselect;
						$the_view = (@$$viewselect)?simplexml_load_string($$viewselect):$viewselect;	
						$html .= x_object::create($the_view )->xhtml($object,false);
						
					}
			
				}
				else $container->log( xevent::warning, "$tag->event_format : $key is not an array and is therefore perhaps not a great choice to render as an x-collection.  It's also possible that you got back an empty result set from the invoked method...");
				
			break;
			// php directive
			case 'php':
				$html = '';
				$include = xQuery::parse(( string) $this->xml['include'], $obj, $attributes);
				if ( $include ) {
					$file = fopen( $include, "r");
					while ( $str = fgets( $file ))
						$html .= $str;
					fclose( $file );
				}
			break;
		
			// paginator
			case 'paginator':
			
				$html = paginator::create( 
					xQuery::parse(( string) $this->xml['class'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['key'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['size'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['query'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['page'], $obj, $attributes)
				)->xhtml;
			
			break;
			
			// x array
			case 'x-array':
			
				$html = x_array::create( 
					xQuery::parse(( string) $this->xml['start'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['end'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['increment'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['view'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['type'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['time_format'], $obj, $attributes),
					xQuery::parse(( string) $this->xml['unique_id'], $obj, $attributes)
					
					
					)->xhtml();
					
			
			break;
			
			// price picker
			case 'x-price-picker':
				
				$html = x_price_picker::create( 
				xQuery::parse( (string) $this->xml['id'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['default'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['low'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['high'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['increment'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['currency'] , $obj, $attributes ),
				
				$obj)->xhtml();
			
			break;

			// height picker
			case 'x-height-picker':
				
				$html = x_height_picker::create( 
				xQuery::parse( (string) $this->xml['id'] , $obj, $attributes ),
				xQuery::parse( (string) $this->xml['default'] , $obj, $attributes ),
				
				$obj)->xhtml();
			
			break;
		
			
			
			// age picker
			case 'x-age-picker':
				
				$html = x_age_picker::create(
					xQuery::parse( (string) $this->xml['min'] , $obj, $attributes ),
					xQuery::parse( (string) $this->xml['max'] , $obj, $attributes ),
					xQuery::parse( (string) $this->xml['id'] , $obj, $attributes ),
					xQuery::parse( (string) $this->xml['default'] , $obj, $attributes ),
					
					
					$obj
				)->xhtml();
			
			break;
		
			// country picker
			case 'x-country-picker':
			
				$html = x_country_picker::create( 
					xQuery::parse( (string) $this->xml['id'] , $obj, $attributes ),
					xQuery::parse( (string) $this->xml['class'] , $obj, $attributes ),
					xQuery::parse( (string) $this->xml['default'] , $obj, $attributes ),
					$obj
					
					)->xhtml();
			
			break;
		
			// content
			case 'x-content':
			
				$html = content::create( 
					(string) $this->xml['key'],
					(string) $this->xml['id'],
					xQuery::parse( (string) $this->xml['class'] ) )->xhtml;
		
			break;
			// time picker
			case 'x-timepicker':
				$html = (string) new x_time_picker(
					xQuery::parse( $this->xml['id'], $obj, $attributes),
                    xQuery::parse( $this->xml['class'], $obj, $attributes),
                    xQuery::parse( $this->xml['default'], $obj, $attributes)

				);
			break;
		
			// x select object
			case 'x-select':

               $key = xQuery::parse( $this->xml['key'], $obj, $attributes);
                if ( ! $key)
                    $html = "You must specify attribute 'key' for x-select element, to specify which database table to query";
				else $html = x_select::create(
                    $key,
					xQuery::parse( $this->xml['values'], $obj, $attributes),
					xQuery::parse( $this->xml['names'], $obj, $attributes),
					xQuery::parse( $this->xml['query'], $obj, $attributes),
					$obj,
					xQuery::parse( $this->xml['id'], $obj, $attributes),
					xQuery::parse( $this->xml['class'], $obj, $attributes),
					xQuery::parse( $this->xml['default'], $obj, $attributes),
					xQuery::parse( $this->xml['title'], $obj, $attributes),
					xQuery::parse( $this->xml['multiple'], $obj, $attributes)
					
					
					
					 )->xhtml();
			
			break;
		
			// a realtip is used to show a popup tip
			case 'realtip':
			
				$html = RealTip::create( 
					(string) $this->xml['key'],
					(string) $this->xml['behavior'],
					(string) $this->xml['class'],
					(string) $this->xml['tip']
				)->xhtml();
			
			break;
			
			// render embedded realobject
			case 'realobject':
			case 'webobject':
			case 'x-object':
			
				// special case for recaptcha
				if ( (string) $this->xml['key'] == 'recaptcha' )
					$html = ReCaptcha::create()->xhtml();
				else {
					$key =  xQuery::parse( (string) $this->xml['key']);
					global $$key;
					if ( is_string( @$$key) && @$$key != "")
						$html = x_object::render( $$key)->xhtml( $obj);
					else 
						$html = x_object::create( 
						xQuery::parse( (string) $this->xml['key'], $obj, $attributes), // pass key to find view
						$this->xml->attributes()    // attributes used to setup view
						)->xhtml( $obj );
				}
			break;
			
			case 'recordset':
			
				// debugging
				if ( $this->debug)
					echo "$tag->event_format : evaluating new <span style='color:green'>recordset</span> tag<br>\r\n";
			
				$html =  RecordSet::create( 
							xQuery::parse( (string) $this->xml['key'] , $obj, $attributes ) , 
							xQuery::parse( (string) $this->xml['query'] , $obj, $attributes) , 
							(string) $this->xml['view'],
							(string) $this->xml['none-view']?(string)$this->xml['none-view']:(string)$this->xml['none_view'],
							(string) $this->xml['group-view']
							)->xhtml();
			
			break;
			
			// action for standard html elements:
			case 'a':
			case 'area':
			case 'br':
			case 'button':
			case 'div':
			case 'dd':
			case 'dt':
			case 'em':
			case 'footer':
			case 'form':
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'header':
			case 'hr':
			case 'i':
			case 'iframe':
			case 'img':
			case 'input':
			case 'label':
			case 'li':
			case 'link':
			case 'login-button':
			case 'map':
			case 'select':
			case 'ol':
			case 'option':
			case 'p':
			case 'script':
			case 'span':
			case 'strong':
			case 'table':
			case 'td':
			case 'textarea':
			case 'tr':
			case 'ul':
			default:
		
				// open the element
				$html = $this->indent() . '<' . $this->xml->getName() . ' ';
		
				// add in attributes as string
				$html .= $this->attribString( $obj , $attributes ) . '>';

				// if we have an object, and requested to use auto id, set it here
				if ( $obj && preg_match( '/id="auto"/' , $html ) ) {
					$id =  $obj->id();
					$html = preg_replace( '/auto/' , $id, $html );
				}
			
				// set inner html
				$html .= trim ( $this->parse_variables( (string) $this->xml , $obj,  $attributes) );
		
				// increase indent
				$this->indent( 1 );
		
				// recurse through children
				foreach ( $this->children() as $child ) {
					$html .= "\r\n" . $child->html( $obj, $attributes );
				}
		
		
				// decrease indent
				$this->indent( -1 );
		
				// add indent for close if necessary
				if (
					count($this->xml->children())			
					//$this->xml->count() 
					)
				$html .= $this->indent();
			
				$html .= '</' . $this->xml->getName() . '>' . "\r\n";
				if ( $container->debug)
					echo "$tag->event_format : done generating xhtml for node of type xhtml $obj<br>\r\n";	
		
			break;

			
		}
		
			if ( $container->debug)
			echo "$tag->event_format : end generating xhtml for $obj<br>\r\n";	
	
		return (self::$css_compatible) ? $this->css_ize( $html ) : $html;

	}
	
	
	//! ie7-ize the html elements 
	private function css_ize( $html ) {
	
		$matches = array();
		$regex = '/class="[a-z|A-Z|\-|_| |0-9]*"/' ;
		if ( preg_match(  $regex, $html, $matches ) )

			foreach ( $matches as $match ) {
				$html = preg_replace ( "/$match/", substr( $match, 0 , strlen( $match) - 1) . " " . self::$css_compatible . '"', $html );
			}
		return $html;
	}
	
	//! returns node name
	public function name() { return $this->xml->getName(); }
	
	//! get all top-level attributes as a string
	public function attribString( $obj = null , $attributes = null ) {
	
		$dont = $obj ? "" : "don't";
		
		$str = '';
		
		foreach ( $this->xml->attributes() as $name => $value ) {
			$str .= "$name=\"$value\" ";
		}

		// check against the new xQuery parsing engine
		$str = xQuery::parse( $str, $obj, $attributes );
		
		
		
		// pull value from a get
		if ( $match = preg_match_all ('/"get:[a-z|A-Z|0-9|_]+"/' , $str, $matches ) ) {
			foreach ( $matches[0] as $match ) {
				
				$member = rtrim( substr( $match, 5) , '"');
				$str = preg_replace ( "/get:$member/", $obj->$member , $str );
			}
		}
		
		// place the record id
		if ( preg_match( '/(\[record_id\]){1}/' , $str ) )
			$str = preg_replace( '/\[record_id\]/' , $obj->id , $str );
		
		// handle auto values
		if ( preg_match( '/value="auto"/' , $str ) ) {
		
			$prop = (string)$this->xml['name'];
			$autoVal = $obj->$prop;
			if ( $this->debug  || Debugger::enabled() )
				Debugger::echoMessage( "RealXML::attribString(): autoval = $autoVal" );
				
			$str = preg_replace( '/value="auto"/' , "value=\"$autoVal\"" , $str );
		}
			
			if ( $this->debug || Debugger::enabled() )
			Debugger::echoMessage( "RealXML::attribString(): $dont have an object for $str" ); 
	
		return $str;
	}
	
	//! indent appropriately for the current level
	public function indent( $change = 0 ) {
	
		static $indent = 1;
		
		if ( $change )
			$indent = $indent + $change;
		else {
			$str = '';
			for ( $i = 1 ; $i <= $indent ; $i++ )
				$str .= "  ";
			
			return $str;	
		}
	
	}
	
	//! returns the raw xml for this object
	public function xml() { return $this->xml; }
	
	//! manufacture a new RealXML
	public static function create( $objOrFile ) { return new RealXML( $objOrFile ); }
	
	//! find a specific node and return it
	public function find( $name ) {
		
		return $this->xml->xpath( $name );
	}
	
	//! parse embedded variables
	private function parse_variables( $string, $obj = null, $attributes = null ) {
	
		// perform xquery
		$string = xQuery::parse( $string, $obj, $attributes );
	
		// look for inline attributes substitutions
		$regex = '/\[@([A-Z|a-z|_|-| |0-9]+){1}\]/';
		if ( preg_match( $regex, $string, $matches ) ) {
			$string = preg_replace( $regex, $attributes[ $matches[1] ], $string);
		}
	
		// look for substitutions for login-service
		$regex = '/(\[){1}(login-service:){1}(([a-z|A-Z|0-9|_])+){1}(\]){1}/';
		if ( preg_match( $regex, $string, $matches) ) {
			$string = preg_replace( $regex, x_objects::instance()->service['login']->$matches[3], $string );
		}
		
		// look for in-line PHP variables
		$regex = '/(\$){1}(([A-Z|a-z|0-9|_|-])+){1}/';
		if ( preg_match( $regex, $string, $matches)) {
			global $$matches[2];
			$string = preg_replace( $regex, $$matches[2], $string); 
		}

		return $string;
	}
	
	
}

?>