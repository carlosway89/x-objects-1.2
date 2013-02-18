<?php
//! class for Message object, which represents an individual message; recipients may be internal users or external emails
class Message extends BusinessObject2 implements Message_interface {

	//! toggle debugging
	private static $debug = false;
	
	//! construct new message
	public function __construct( $search ) {
	
		// construct parent
		parent::__construct( get_class() , $search );
		
		// if no thread id, create it
		if ( ! $this->get('thread_id') )
			$this->set('thread_id', DBMetrics::metric( 'max' , 'message' , null, 'thread_id') + 1);
		
	}
	
	//! get/set the message subject
	public function subject( $subject = null ) {

		return $this->get('subject');
	}
	
	
	//! get message to
	public function msg_to() { return $this->get('msg_to'); }
	
	//! get / set the recipients
	public function recipients ( $recipients = null ) {
	
		return true;
	}
	
	//! get / set the message body
	public function body ( $text = null ) {
	
		return $this->get('body');
		
	}
	
	//! save a draft of the message
	public function saveDraft() {
	
		return true;
	}
	
	//! send the message
	public function send() {
	
		return true;
	}
	
	//! archive/recycle the message
	public function archive() {
	
		return true;
	}
		
	//! mark the message as spam
	public function markAsSpam() {
	
		return true;
	}
	
	//! get / set read status
	public function read( $read = null ) {
	
		return true;
		
	}
	
	//! unarchive the message
	public function unarchive() {
	
		return true;
		
	}
	
	//! add an attachment to the message
	public function attach( $attachment ){
	
		return true;
		
	}
	
	//! remove an attaachment from the message
	public function unattach( $id ){
	
		return true;
	}
	
	//! get datasource in static context
	public static function source() {
	
		return new DataSource2( RealXML::create( 'bo-message' )->xml()->datasource );
	}
	
	//! return as well-formed html
	public function html( $view = "message-list-view" ) {
	
		if ( self::$debug || Debugger::enabled() )
			Debugger::echoMessage( "Message::html( $view )" );
			
		// create as a web snippet and return as html
		return WebObject::get( $view )->html( $this );
		
	}
	
	//! returns the name of the user who sent the message
	public function from() {
	
		return "FIRSTNAME LASTNAME";
	}
	
	//! returns message date created
	public function date_created() {
	
		return date( 'F j, Y' , strtotime( $this->get('date_created') ) ) .
			' at ' .
			date( 'g:i a' , strtotime( $this->get('date_created') ) );
		
	}
	
	//! manufacture a new message, with an optional search to load an existing one
	public static function create( $search = null ) {
		return new Message( $search );
	}
	
	//! get a reply message for the current one
	public function reply() {
	
		// clone the current message
		$reply = clone $this;
		
		// unset id
		$reply->set('id', '');
		
		// set the subject
		$reply->set( 'subject' , 're: ' . $reply->get('subject'));
		
		// recipient is sender
		$reply->set( 'msg_to' , $reply->get('msg_from'));
		
		// sender is the current user
		$reply->set( 'msg_from' , 1 );
		
		// format body as a reply
		$reply->format_body_as_reply();
		
		// return the new message
		return $reply;
	}
	
	//! format the body of a message as a reply/forward
	public function format_body_as_reply() {
	
		$this->set( 'body' ,
			"\r\n\r\n<-- on ".$this->date_created()." ".$this->msg_to()." wrote: -->\r\n".$this->body());
	
	}
}
?>