<?php
//! public interface for Message object, which represents an individual message; recipients may be internal users or external emails
interface Message_interface {

	//! inbox context
	const INBOX = 1;
	
	//! get/set the message subject
	public function subject( $subject = null );
	
	//! get / set the recipients
	public function recipients ( $recipients = null );
	
	//! get / set the message body
	public function body ( $text = null );
	
	//! save a draft of the message
	public function saveDraft();
	
	//! send the message
	public function send();
	
	//! archive/recycle the message
	public function archive();
		
	//! mark the message as spam
	public function markAsSpam();
	
	//! get / set read status
	public function read( $read = null );
	
	//! unarchive the message
	public function unarchive();
	
	//! add an attachment to the message
	public function attach( $attachment );
	
	//! remove an attaachment from the message
	public function unattach( $id );
	
	
	
}
?>