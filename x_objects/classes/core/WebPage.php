<?php
/**
 * a WebPage() -- synonym web_page() -- represents the data and process associated with rendering a specific
 * user-viewable page in the browser
 */ 
 class WebPage {
 	// toggle debugging
	private $debug = false;
 
	//! the key
	private $key = '';
	
	//! does the page require login?
	public $requires_login = false;
	
	//! doctypes
	private $docType = array (
		'strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
		'trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
		'html5' => '<!DOCTYPE html>'
	);
	
	//! xml, from configuration
	private $xml = null;
	
	//! holds records required by the page
	private $records = array();
	
	//! construct a new WebPage, given a key
	public function __construct( $key ) {
		global $container, $pathroot, $directory_name, $webapp_location;
		$tag = xo_codetag::create( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__); 
	
		// set up possible locations
		$locations = array(
			$webapp_location . "/app/views/pages/$key.php",
			$pathroot.$directory_name."views/pages/$key.php"			
		);
						
		$this->key = $key;
		
		if ( $container->debug )
			echo "$tag->event_format : key = $key<br>\r\n";
		
		$found = false;
		foreach ( $locations as $l){
			if ( $container->debug)
				echo "$tag->event_format: looking for view $l<br>\r\n";
			if ( file_exists($l)){
				require_once( $l);
				$found = true;
				break;
			} else {
				if ( $container->debug)
					echo "$tag->event_format: view $l file does not exist<br>\r\n";
				
			}
					
		}
		if ( $container->debug)
			echo "$tag->event_format : page ". ( $found?"":"not")." found<br>\r\n";
		
	}
 
	//! display the page
	public function display() {
		echo $this->xhtml();
	}
	
	//! get the page as well-formed xHTML
	public function xhtml() {
		
		if ( ! $this->xml)
			return;
	
		// get the webroot
		global $pathroot, $webroot, $container,$xobjects_location,$directory_name;
		
		// get the app name
		$appname = $container->app->name;
	
		
		// collate doctype
		$html = $this->docType[ $this->xml->attr( 'doctype' ) ] . "\r\n";
		
		// wrap everything in html tag
		$html .= '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">' . "\r\n";
		
		// construct the head
		$html .= "<head>\r\n";
		
		// set cntent type
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		
		/*
		 * new change per Ivan Shcherbak, we need a way to template the <head> section.
		 * For now, if its missing from the page, we assume it can be found in /xml/head.xml
		 */
		 $head = ( isset( $this->xml->xml()->head ))? $this->xml->xml()->head : $this->get_head();
		 	
		// if we have head items, process them
		foreach ( $head->children() as $child ){
			switch ( $child->getName() ) {
				// javascript library include
				case 'js':
					$html .= js_lib_bundle::create( $child["libs"] )->xhtml;
				break;
				// meta
				case 'meta':
					$html .= "<meta ";
					foreach ($child->attributes() as $name => $value )
						$html .= " $name=\"$value\" ";
					$html .= "/>";
				break;
				// doc title
				case 'title':
					$html .= '<title>'. (string) xQuery::parse( $child ) .  '</title>';
				break;
				// style sheets
				case 'css':
					$html .= "  " . '<link rel="stylesheet" type="text/css" media="all" href="' . xQuery::parse( (string) $child ) . '"></link>' . "\r\n";
				break;
				// javascript
				case 'script':
					if ( isset( $child['src'] ) ) {
						$html .= "	" . '<script type="text/javascript" src="' . xQuery::parse( (string) $child['src'] ) . '"></script>'. "\r\n";
						if ( preg_match ("/$appname\.js/", $html ) )  {
//							echo get_class() . ": xhtml: just loaded app js<br>";
							// auto load page js
							if ( file_exists( $pathroot . "js/$this->key.js" ) )
								$html .= '<script type="text/javascript" src="' . $webroot . "js/$this->key.js"  . '"></script>'. "\r\n";
						}	
						
					}
					else $html .= "	" . '<script type="text/javascript">' .  xQuery::parse( $child ). '</script>' . "\r\n";
				break;
				default:
					$container->exception(
						// type of exception
						xexception::unrecognized_tag,
						// code tag
						new xo_codetag( __FILE__, __LINE__, get_class(), __FUNCTION__),
						// data
						"The xml tag tagname=".$child->getName()." was not recognized by the parser, when trying to render the WebPage(). "
					);
				break;
			}
			//! add app js if necessary
			if ( $child->getName() == 'js') {
				if ( file_exists( $pathroot . "js/$appname.js"))
					$html .= '<script type="text/javascript" language="javascript" src="'.$webroot."js/$appname.js".'"></script>';
			}
		}
			
		// auto load page css
		if ( file_exists( $pathroot . "css/$this->key.css" ) )
			$html .= '<link rel="stylesheet" type="text/css" media="all" href="' . $webroot . "css/$this->key.css"  . '"></link>'. "\r\n";
		
		
		
		$html .= "</head>\r\n";
		
		// and the body
		$html .= '<body class="' . $this->key . '">' . "\r\n";
		
		// add special div for missing controller
		$html .= '<div class="missing-controller">' . xQuery::parse( "[var:missing_controller]" ) . '</div>' . "\r\n";
		
		// loop through all elements and create them
		foreach ( $this->xml->xml()->body->children() as $node ) 
			
			$html .= RealXML::create( $node )->html();
				
				
		$html .= "</body>\r\n";
	
		// close html
		$html .= '</html>';
		
		// manage embedded calls
		$html = $this->function_calls( $html );
		
	
		
		return $html;
	}
	
	// handle embedded function calls
	private function function_calls( $html ) {
	
		$regex = '/\[call:[A-Z|a-z|0-9|_|-]+\.[A-Z|a-z|0-9|_|-]+\]/';
		if ( preg_match( $regex, $html, $matches ) )
			foreach ( $matches as $match ) {
				//echo "match = $match <br>";
				if ( preg_match( '/[A-Z|a-z|0-9|_|-]+\.[A-Z|a-z|0-9|_|-]+/' , $match, $matches2 ) ) {
					$evalue = call_user_func( implode( '::' , explode( '.' , $matches2[0] ) ) );
					//echo "evalue = $evalue <br>";
					$html = preg_replace( '/' . preg_quote( $match ) . '/', $evalue , $html);
				}
			}	
		return $html;
	}

	//! create a new web page
	public static function create( $key ) {
		return new WebPage( $key );
	}
	
	/*
	 * get the head, ither from an xml file, or from the string 
	 */
	private function get_head(){
		// first see if its defined as a string
		global $head_template;
		if ( isset( $head_template)){
			$head = simplexml_load_string( $head_template);
			return $head;
		} else {
			$head = new RealXML("head_template");
			return $head->xml();
		}
	}
	
}

?>