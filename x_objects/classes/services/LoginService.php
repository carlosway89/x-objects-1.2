<?php 
/*! Provides simple services to display login forms, log users in and out, and verify 
 */

class LoginService {		// for database access

	//! flag to enable debugging for this module
	private static $Debug = true;

	//! error message from last login failed attempt
	public static $error = null;
	
	//! validation spec to use for login
	private $spec = null;
	
	//! class to use when loading users
	private $class = null;
	
	// constants defined for session variable names
	const LS_ISLOGGEDIN = 'LS_isLoggedIn';				// set if user logged in
	const LS_USERNAME = 'LS_Username';					// used to store username
	const LS_USERTYPE = 'LS_Usertype';					// stores user type
	const LS_USERID = 'LS_UserId';						// stores user id in session
	const LS_FIRSTNAME = 'LS_Firstname';				// stores the firstname
	
	// form constants
	const LS_USERNAME_FIELDNAME = 'txtEmail';
	const LS_PASSWORD_FIELDNAME = 'txtPassword';
	
	//! constant to specify that login data was posted
	const LS_LOGIN_DATA = 'logindata';
	
	//! html class and id constants
	const LS_CSS_CLASS_PREFIX = 'lsclass_';
	
	// login return results
	const LS_BAD_UNAME_PWD = 2;							// username and password don't match or are wrong
	
	//! enable/disable user account activation by an administrator
	private static $useActivation = true;
	
	//! the instance (singleton)
	private static $instance = null;
	// cache some stuff
    private $cache = null;
	//! construct a new login service
	private function __construct() {
        $this->cache = new magic_object();
		// obtain the login user class and instantiate the user if available
	    if ( isset( $_SESSION['uid'])){
            $classname = @$_SESSION['uclass']?$_SESSION['uclass']:"user";
            $this->cache->user = new $classname( "id='" . @$_SESSION['uid'] . "'");
        }
	}
	
	//! magic get
	public function __get( $what ) {
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug) echo "$tag->event_format: getting $what<br>\r\n";
        $result = null;
        switch( $what ) {
            case 'user':
                if ( ! $this->cache->user ){
                    $c = @$_SESSION['uclass'];
                    $this->cache->user = $c?new $c("is_deleted=0,id='". $_SESSION['uid'] ."'"):null;
                }
                $result = $this->cache->user;
            break;
			case 'me':
	            $result = $this->user;
                //echo "me = ".$result;
			
			break;
		
			case 'user_type':
			
				$result = @$_SESSION['utype'];
				
			break;
		
			case 'uid':
			
				$result = @$this->user->id;
				
			break;
			
			case 'username':
				$result =  isset( $this->user ) ? $this->user->username : 'guest' ;
			break;
		}
        if ( $container->debug) echo "$tag->event_format: result of $what is $result<br>\r\n";

        return $result;
		
	}
		//! returns a reference to the singleton instance of the class
    public static function instance() 
    {
		// if the instance hasn't been created yet
        if (!isset(self::$instance)) {
			// use the current classname
            $C = __CLASS__;
			// and create the instance as a new object of that class
            self::$instance = new $C;
        }

		// return a reference to the instance
        return self::$instance;
    }
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

			
	//! check if a token is set in the user session or cookie, which means the user is logged in
	 
	public static function is_logged_in() {
		
		// check if cookie is set and valid, and if so, bypass login
		if ( isset( $_COOKIE['userlogin'])) {
			
			$cookieValue = explode(',', $_COOKIE['userlogin']);

			// get the user record
			$class = LS_LOGIN_TABLE;
			$user = new $class( $cookieValue[0], true);
			
			// if user invalid or inactive, return false
			if ( ! $user->get('isActive') || ! $user->get('isValid'))
				return false;
			
			if ( ! strcmp( $_COOKIE['userlogin'], $user->get('cookie'))) {
				
				return true;
				
			} else return false;
			
			
		}
		
		// check login
		else {
			return ( isset($_SESSION['uid']) ? true : false ); 
			
		}
	}
	
	//! checks whether the user must change their password
	public function mustChangePassword() {
	
		// get a copy of the application configuration
		$conf = new AppConfiguration();
		
		$class = LS_LOGIN_TABLE;
		
		// create a new user record
		$user = new $class( $_REQUEST[LS_USERNAME_FIELDNAME], true );
	
		// get the age of the password in months
		$pwd_age = Utility::get_time_difference( $user->get('password_age'), date('Y-m-d',time()));
	
		if ( $conf->getMaxPasswordAge() && $pwd_age['days'] > ( $conf->getMaxPasswordAge() * 30))
			return true;
		else return false;
		
	}
	
	//! returns the username field name
	public static function usernameField() {
		return defined( 'LS_USERNAME_FIELDNAME' ) ? LS_USERNAME_FIELDNAME : 'Username' ;
	}
	
	//! returns the password field name
	public static function passwordField() {
		return defined( 'LS_PASSWORD_FIELDNAME' ) ? LS_PASSWORD_FIELDNAME : 'Password' ;
	}
	
	
	//! checks user credentials and logs in if successful, otherwise returns false

	public function set_logged_in( $user ) {
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);


		$_SESSION['uid'] = $user->id;
		//echo $_SESSION['uid'];
		
		$_SESSION['uclass'] = get_class( $user );
		$_SESSION['utype'] = $user->type;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['full_name'] = $user->firstname. " ".$user->lastname;

        return true;
				
	}
	
	//! change the password for myself (a user who has logged in)
	public function changePassword( $user ) {
		
	}
	
	public function logout() { 
		
		// unset session
		unset($_SESSION['uid']);
		
		// remove the cookie, if present
		if ( isset( $_COOKIE['userlogin'] ))
			setcookie( 'userlogin', $_COOKIE['userlogin'], time() - 600);
		 
	}
	
	/*
	 * get(): get a session variable
	 */
	public function get( $Key ) { return $_SESSION[$Key]; }
	/*
	 * set(): set a session variable
	 */
	public function set( $Key, $Value ) { $_SESSION[$Key] = $Value; }
	
	/*
	 * getCurrentUser(): return the name of the current user logged in
	 * @returns: (string) name of current user, or null if none
	 */
	public static function getCurrentUser( ) { return isset( $_SESSION[self::LS_USERNAME]) ? $_SESSION[self::LS_USERNAME] : null; }

	//! returns the number of failed attempts in this session, useful for user notice about lock accounts
	public function failedAttempts() {

		if ( ! isset( $_REQUEST[LS_USERNAME_FIELDNAME]))
			return 0;
			
		$user = new $this->class($_REQUEST[LS_USERNAME_FIELDNAME], true);

		if ( $this->Debug) 
			Debugger::echoMessage(get_class() . '::' . __FUNCTION__ . '(): login failed attempts =' . $user->get('failedLogins') );
				
		return $user->get('failedLogins');		
	}
	
	//! get my user type
	public static function getMyUserType() { return isset( $_SESSION[self::LS_USERTYPE]) ? $_SESSION[self::LS_USERTYPE] : 'none';
	}
	
	//! synonym for myUserId()
	public function my_id() {

		return $this->myUserId();
		
	}
	
	//! get my userid
	public function myUserId() {
		return isset( $_SESSION['uid'] ) ? $_SESSION['uid'] : null;
	}
	
	//! feign being logged in as a specific user
	public function feign( $id ) {
		$_SESSION[self::LS_USERID] = $id;
		
	}
	
	
	//! get my firstname
	public static function myFirstname() {
		return isset( $_SESSION[self::LS_FIRSTNAME] ) ? $_SESSION[self::LS_FIRSTNAME] : null;
	}
	
	//! has login data been posted by the user?
	public static function dataPosted() {
		return isset( $_REQUEST[self::LS_LOGIN_DATA] );
	}
}
?>
