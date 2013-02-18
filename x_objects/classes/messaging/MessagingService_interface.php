<?php 
//! public interface for the messaging service module
interface MessagingService_interface {

	//! default retrival limit for message
	const RETRIEVE_LIMIT = 10;
	
	//! create a new message
	public function newMessage( $subject, $body, $recipients );
	
	//! send message
	public function send( $id );
	
	//! gets some or all messages, to display as inbox, for example
	public static function retrieve(
		$who,								// retrieve for which userid?
		$context = self::INBOX,			// by default retrieve all
		$startWith = 0,						// by default, start with first message
		$limit = self::RETRIEVE_LIMIT		// batch retrievals for pagination
		);
	
	//! mark messages as unread
	//! \param $ids (array) of message ids
	public function markUnread( $ids );
	
	//! mark messages as read
	public function markRead( $ids );
	
	//! archive message
	public function archive( $ids );
	
	//! unarchive messages
	public function unarchive( $ids );
	
	//! opens a specific messsge to view/edit
	public function open( $id );
	
	//! count messages in a given context
	public static function count( $context , $scope);
	
	//! get current context name
	public static function contextName();
	
	//! get formatted msg counter
	public static function msgCounter( $context );
	
	//! search for messages
	public static function search ( $query );
	
	

}