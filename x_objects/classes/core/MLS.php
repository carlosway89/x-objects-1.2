<?php
/*! class to manage multi-language support
 * 
 */
final class MLS {
	
	// the current language
	private static $language = 'en';
	
	//! enable multi-language support within the current scope
	public static function enable() {
	
		$separator = RUNNING_ENV == 'stage' ? ':' : ';';
		
		$bundle = PATHROOT . 'lang/' . self::getCurrentLanguage();
		
		ini_set( 'include_path' , ini_get('include_path') . $separator . $bundle );
				
		require_once( 'labels.php' );
					
		require_once( $_SERVER['DOCUMENT_ROOT'] . '/unionsmart/lang/' . self::getCurrentLanguage() . '/labels.php' );
		
		// log a message that MLS has been started
		EventLogManager::instance()->log( 'Multi-language support enabled, language file loaded = ' . $bundle . '/labels.php' , EventLogManager::LOG_INFO ); 
	}
	
	//! get the currently set language (if not set, use default)
	public static function  getCurrentLanguage() {
		
		return self::$language;	
		
	}
	
}
?>