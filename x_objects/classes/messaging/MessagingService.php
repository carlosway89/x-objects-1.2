<?php 
//! class for the messaging service module
class MessagingService implements MessagingService_interface {

	//! current context
	private static $context = 'inbox';
	
	//! create a new message
	public function newMessage( $subject, $body, $recipients ) {
	
		return true;
	}
	
	//! send message
	public function send( $id ) {
	
		return true;
		
	}
	
	//! gets some or all messages, to display as inbox, for example
	public static function retrieve(
		$who,								// retrieve for which userid?
		$context = "inbox",			// by default retrieve all
		$startWith = 0,						// by default, start with first message
		$limit = self::RETRIEVE_LIMIT		// batch retrievals for pagination
		) {
		
		// translate context into something meaningful
		$cond = '';
		switch ( $context ) {
		
			case "inbox":
			
				$cond = "msg_to='$who',is_deleted='0'";
				
			break;
	
			case "sent":
			
				$cond = "msg_from='$who'";
				
			break;
			
			case "trash":
			
				$cond = "msg_to='$who',is_deleted='1'";
				
			break;
	
		}
		
		return ObjectFactory::create( 'Message' , $startWith, $limit, 'date_created', 'ASC' , $cond );
		
	}
	
	//! mark messages as unread
	//! \param $ids (array) of message ids
	public function markUnread( $ids ) {
	
		return true;
	}
	
	//! mark messages as read
	public function markRead( $ids ) {
	
		return true;
		
	}
	
	//! archive message
	public function archive( $ids ) {
	
		return true;
	}
	
	//! unarchive messages
	public function unarchive( $ids ) {
	
		return true;
		
	}
	
	//! opens a specific messsge to view/edit
	public function open( $id ) {
	
		return true;
		
	}

	//! count messages within a context and scope
	public static function count( $context , $scope) {
	
		$cond = '';
		if ( $context == "inbox" )
			$cond = "msg_to='1',is_deleted='0'";
		elseif ( $context == "sent" )
			$cond = "msg_from='1'";
		else $cond = "is_deleted='1',msg_from='1'";
		
		if ( $scope == "read" || $scope == "unread" )
			$cond .= ",is_read='" . ($scope == "read" ? '1' : '0') . "'";
		return DBMetrics::metric( DBMetrics::M_TOTAL, 'message' , $cond );
		
	}

	//! get current context name
	public static function contextName() { return ucfirst( self::$context ); }
	
	//! get formatted message counter
	public static function msgCounter( $context)	{
	
		if ( $count = self::count( $context, "all" ) )
			return "(<span id=\"inbox_count\" class=\"font19\">$count</span>)";
			
	}

	//! search for messages
	public static function search( $query ) {
	
		return ObjectFactory::search ( 'Message' , 'RLIKE' , $query );
		
	}
	
}