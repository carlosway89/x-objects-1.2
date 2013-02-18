<?php
/**
 * an email_message() is an object representation of a specific email message, that was either retrieved from a POP3
 * server, or instantiated directly from STDIN (such as when forwarded to a script).
 *
 */
class email_message extends magic_object {

	/**
	 * create a new email_message()
	 * @param string $raw_msg the raw message text, before it is parsed
	 * @param integer $id the unique message id from the POP3 server
	 */
	public function __construct($raw_msg,$id){
		// reference the container
		global $container;
		// create a tag
		$tag = new xo_codetag(xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		// toggle debugging
		$this->debug = (Debugger::enabled())?true:$this->debug;
		$this->id = $id;
		// try to match this for body
		if ( preg_match( '/Content\-Transfer\-Encoding:\squoted\-printable/',$raw_msg,$matches)) {
			$parts = explode( 'quoted-printable',$raw_msg);
			$this->body = $parts[1];
		}
		// first case
		if ( preg_match( '/X\-DH\-Original\-To\:\s+join\@txtapet\.com/',$raw_msg,$matches)) {
			$parts = explode('X-DH-Original-To: join@txtapet.com',$raw_msg);
			if ( count($parts) > 1)
				$this->body = trim($parts[1]);
			else $this->body = "failed to split by X-DH";
			// check for a boundary
			if ( preg_match( '/boundary="(.*)"/',$raw_msg,$matches)
				|| 
				preg_match( '/boundary=([a-z|A-Z|0-9]+)/',$raw_msg,$matches)
				){
				$this->boundary = $matches[1];
				$parts = split($this->boundary,$this->body);
				if ( count($parts) > 1)
					$this->body = $parts[1];
				if ( preg_match( '/7bit/',
					$this->body, $matches)) {
					$parts = explode("7bit",$this->body);
					if ( count($parts) > 1)
						$this->body = rtrim($parts[1],'-');
				}
			}
			
		}
		elseif ( preg_match( '/boundary="(.*)"/',$raw_msg,$matches)
			|| 
			preg_match( '/boundary=([a-z|A-Z|0-9]+)/',$raw_msg,$matches)
		){
			$this->boundary = $matches[1];
			$container->log( xevent::debug , "$tag->event_format : new email boundary=$this->boundary");
			$parts = explode($this->boundary,$raw_msg);
			if ( count($parts))
				$this->body = $parts[2];
			// further refinement
			$parts = explode("7bit",$this->body);
			if ( count($parts) > 1)
				$this->body = $parts[1];
			//if ( $this->debug) echo  " " . __LINE__. " " . get_class() . "{} ". __FUNCTION__ . " got " . count($parts) . " parts<br><br>";	
		} else {
			// vtext
			//xevent::log(xevent::notice,__FILE__."( ".__LINE__." ) ::". get_class() ."(): ".__FUNCTION__."(): ".
			//" attempting to parse VTEXT message... raw= $raw_msg ");
			$parts = explode("X-Spam-Flag: NO",$raw_msg);
			if ( count($parts) > 1){
				$this->body = $parts[1];
				//xevent::log(xevent::notice,__FILE__."( ".__LINE__." ) ::". get_class() ."(): ".__FUNCTION__."(): ".
			//" VTEXT message body = $this->body ");
			
			}
			else {} 
						//xevent::log(xevent::notice,__FILE__."( ".__LINE__." ) ::". get_class() ."(): ".__FUNCTION__."(): ".
			//" FAILED to parse VTEXT message... ");

		}
		// get from based on authenticated sender
		if ( preg_match( '/Authenticated sender\:(\s+([0-9]+)@(([a-zA-Z0-9\-_]+)\.){1,2}([a-z|A-Z]+){1})/',$raw_msg,$matches))
			$this->from = trim($matches[1]);
		// get from, but only if sent from a telephone
		elseif ( preg_match( '/<([0-9]+)@(([a-zA-Z0-9\-_]+)\.){1,2}([a-z|A-Z]+){1}>/',$raw_msg,$matches))
			$this->from = trim($matches[0],"<>");
		// get from with basic from
		elseif ( preg_match( '/From:\s+(([0-9]+)@(([a-zA-Z0-9\-_]+)\.){1,2}([a-z|A-Z]+){1})/',$raw_msg,$matches))
			$this->from = trim($matches[1]);
		// another way to get body
		if ( preg_match( '/\(IMP\)\s4.2.2\s+(.*)/',$raw_msg, $matches))
			$this->body = $matches[1];
		
		// additional parsing for sprintpcs
		if ( ! $this->body && preg_match( "/Content\-Transfer\-Encoding\:\s7bit/",$raw_msg,$matches)) {
			$parts = explode( "Content-Transfer-Encoding: 7bit",$raw_msg);
			if ( count($parts) > 1)
				$this->body = $parts[1];
		}
		
		//! verizon
		if ( preg_match( '/charset\=us\-ascii/',$this->body,$matches)) {
			$parts = explode( 'charset=us-ascii',$this->body);
			if ( count($parts)>1)
				$this->body = $parts[1];
		}
		
		
		// perform some cleanup
		$this->body = trim( trim($this->body,"-"));
		
		// for debugging
		$container->log( xevent::debug,"$tag->event_view : new email message created: <br><br>raw=$raw_msg<br><br> from=$this->from<br><br> body=$this->body<br><br> ");		
	}

	// magic get
	public function __get( $what ){
	
		switch($what){
			// is it sms?
			case 'is_sms':
				return (preg_match( '/([0-9]+)@(.*)/', $this->from))?true:false;
			break;
			default:
				return parent::__get($what);
			break;
				
		}
	
	}
	
	/**
	 * create the message by reading the email text from standard input
	 */
	public static function get_from_stdin(){
		global $container;
		$tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
		//Listen to incoming e-mails
		$sock = fopen ("php://stdin", 'r');
		$email = '';
		//Read e-mail into buffer
		while (!feof($sock)) {
			$email .= fread($sock, 1024);
		}
		//Close socket
		fclose($sock);
		if ( $email ) {
			$container->log( xevent::debug, "$tag->event_format : read new raw email from stdin = $email");
			// return new msg
			return new email_message( $email, -1);
		} else {
			$container->log( xevent::error, "$tag->event_format : the new email message was empty, because STDIN passed no data.");
			return null;
		}
	}

}
?>