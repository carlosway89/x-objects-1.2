<?php
/*! Class with static methods for general application administration functions.
 * 
 */
final class Administration {
	
	//! class for loading user records
	private static $userClass = 'User';
		
	//! notify all administrators of a new user
	public static function notifyAdminsOfNewUser( $id ) {
		
		$appConfig = new AppConfiguration();
		
		$class = $appConfig->get('appPrefix') . self::$userClass;
		
		// load the user record
		$user = new $class( $id, true);
		
		// load the admins
		$admins = ObjectFactory::create( $class, null,null,null,null,'notify=\'1\' AND Type=\'administrator\'');

		// pass through each admin and send an email
		foreach ( $admins as $admin)
			Utility::sendEmail(
				$admin->get('txtEmail'),
				'A new user account requires action',
				self::$emailHeaders,
				self::$newUserEmailMsg
			);		
		
	}
	
}
?>