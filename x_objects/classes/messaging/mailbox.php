<?php
//! mailbox instantiates a pop3 connection to a specific mailbox
class mailbox extends magic_object {

	// pop3 connection
	private $pop3 = null;
	
	// debugging
	private $debug = false;
	
	public function __construct($host,$uname,$pass){
		// toggle debugging
		$this->debug = (Debugger::enabled())?true:$this->debug;
		if ( $this->debug)
				echo __FILE__ . " " . __LINE__. " " . get_class() . "{} ". __FUNCTION__ . " host=$host,uname=$uname,pass=$pass<br>";
		$this->host = $host;
		$this->uname = $uname;
		$this->pop3 = new pop3($host,$uname,$pass);
	}

	//! quit upon destruct
	public function __destruct(){
		$this->pop3->pQUIT();
	}
	
	// magic get
	public function __get( $what ){
	
		switch($what){
			// get only sms messages
			case 'sms_messages':
				$msgs = $this->messages;
				$sms = array();
				foreach( $msgs as $msg)
					if ( $msg->is_sms)
						array_push( $sms, $msg);
				return $sms;
			break;
			// get mailbox messages
			case 'messages':
				$msgs = array();
				foreach ( $this->pop3->pLIST() as $msg)
					$msgs[$msg] = new email_message( $this->pop3->pRETR($msg), $msg);
				return $msgs;
			break;
			default:
				return parent::__get($what);
			break;
				
		}
	
	}
	//! delete a message
	public function delete($id){
		$this->pop3->pDELE($id);
		//xevent::log(xevent::notice,__FILE__."( ".__LINE__." ) ::". get_class() ."(): ".__FUNCTION__."(): ".
			//	"POP3 msg id=$id marked for deletion from mailbox [$this->host]/$this->uname : ");
	}
	
	// create a new mailbox
	public static function create($host,$uname,$pass){ return new mailbox($host,$uname,$pass); }

}
?>