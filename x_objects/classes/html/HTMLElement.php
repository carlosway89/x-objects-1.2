<?php 
/*
 * @Project:	Platform
 * @Module:		HTMLElement
 * @Extends:	AttributedObject (objects that can set/get)
 * @Implements:	DisplayableObject (objects that can be displayed)
 * @Created:	Sept 26, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	Object representation of an HTML Element
 */
abstract class HTMLElement extends AttributedObject implements DisplayableObject {

	
	// private debugging
	private $Debug = false;
	
	//! display mode
	private static $displayMode = DisplayableObject::DISPLAY_DEFAULT;
	
	// holds the element name
	protected $ElementName;
	
	// represents the innerHTML for this Div
	private $InnerHTML = '';

	// javascript event handlers
	public $JSHandler;
	
	/*
	 * In a few cases, such as with textarea elements, sometimes we need to add a management span (e.g. to tell
	 * the user how many characters remain. Normally, however, this isn't used
	 */
	 private $managed = null;
	
	function __construct($Attributes, $InnerHTML, $displayOnConstruct) {
		
		// initialize event handlers
		$this->JSHandler = new JavaScriptEventHandler();
		
		if ( $Attributes ) 
			$this->setAttributes( $Attributes , ',' );
		
		if ( $InnerHTML) {
			$this->setInnerHTML( $InnerHTML );
		}
				
		if ( $displayOnConstruct)
			$this->display();
		
	}
	
	/// append one or more new CSS classes to the element
	
	public function addClass( $Class) { 
		$NewClass = $this->get('class') != '' ? $this->get('class') . ' ' . $Class : $Class;
		$this->set('class', $NewClass);
	}
	
	/*
	 * getId(): returns the HTML Id (if defined) for this element
	 */
	public function getId() { return $this->get('id'); }
	
	
	//! get as well-formed html
	public function html() { return $this->getAsHTML(); }
	
	/*
	 * getAsString(): returns a string representation of the element
	 * @returns: (string) element as a string
	 */
	public function getAsHTML() {
	
		$JS = ( $this->JSHandler ) ? $this->JSHandler->getHandlersAsString() : '';
		
		$Indent = '';
		if ( $this->ElementName == 'li')
			$Indent .= "\t";
		
		$html = $Indent . '<' . 
			$this->ElementName . ' ' .  
			$JS . ' ' .
			$this->getAttributesAsString() . '>'  . 
			$this->getInnerHTML() . "\r\n" . '</' . 
			$this->ElementName . '><!-- end ' . 
			$this->get('id') . '-->' . "\r\n";

		// if the field is managed add the management span to the html
		if ( $this->managed )
			$html .= $this->managed->getAsHTML();
			
		return $html;
	}
	
	/*
	 * getInnerHTML(): returns inner HTML as string
	 * @returns: the inner HTML as a formatted string
	 */
	 public function getInnerHTML() { return $this->InnerHTML; }
	
	/*
	 * setInnerHTML( $String ): sets inner HTML to given string
	 * @String: formatted string of inner HTML
	 * @returns: the inner HTML (for chained invocation)
	 */
	 public function setInnerHTML( $Arg ) { 
	 	
	 	$Result = '';
	 	// if it's an object, get the html of the objec
	 	if ( is_object( $Arg))
	 		$Result = $Arg->getAsHTML();
	 	
	 	// recurse through arrays 
	 	elseif ( is_array( $Arg ))	{
	 		
	 		if ( $this->Debug )
	 			Debugger::echoMessage('HTMLElement::setInnerHTML(): beginning recursion');
	 		
	 		$Result = '';
	 		foreach ( $Arg as $Element ) 
	 			if ( $Element )
	 				if ( is_object( $Element) ) { 
	 					try { $Result .= $Element->getAsHTML(); } 
	 				catch ( Exception $e)
	 				{ throw $e; }
	 				} elseif ( is_array( $Element)) {
	 				$Result .= $this->setInnerHTML( $Element);	
	 				}
	 					
	 				else $Result .= $Element; 
	
	 		if ( $this->Debug )
	 			Debugger::echoMessage( 'HTMLElement::setInnerHTML(): recursive Result=' . $Result);
	 		
	 		return $this->InnerHTML = $Result;
	 		
	 	}
	 	
	 	// if we have an object list, handle it here
	 	if ( is_object( $Arg) && get_class( $Arg) == 'ObjectList')
	 		return $Arg->getAsHTML();
	 	// if we have an object, get its string representation
	 	
	 	if ( is_object( $Arg ))
	 		return $this->InnerHTML = $Arg->getAsHTML();
	 	else return ( $this->InnerHTML = $Arg ); 
	 
	 }
	/*
	 * @display(): displays this Div as formatted HTML
	 * @returns: none
	 */
	 public function display() { echo $this->getAsHTML(); }
	
	//! set display mode
	public static function setDisplayMode ( $mode ) { self::$displayMode = $mode; }
	//! get display mode
	public static function getDisplayMode() { return self::$displayMode; }
	
	//! set the management for this element
	public function setManaged( $span ) {
	
		if ( ! is_object( $span ) || ! get_class( $span ) === 'SpanElement' )
			throw new IllegalArgumentException('HTMLElement::setManaged(): argument must be an object of class SpanElement');
		$this->managed = $span;
	}
	
}
?>
