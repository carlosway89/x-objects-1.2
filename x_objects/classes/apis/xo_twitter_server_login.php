<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 23/01/13
 * Time: 12:10 AM
 */
class xo_twitter_server_login {
    private $uri, $ses,$req,$user,$config;
    public function __construct(){
        global $container;
        $this->uri = new REQUEST_URI();
        $this->ses = new SESSION();
        $this->req = new REQUEST();
        // get configuration
        $this->config = $container->config->twitter;
    }
    public function go(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        /* Build twitteroauth object with client credentials. */
        $connection = new twitteroauth(
            (string)$this->config->consumer_key,
            (string)$this->config->consumer_secret);

        /* Get temporary credentials. */
        $request_token = $connection->getRequestToken((string)$this->config->oauth_callback);

        /* Save temporary credentials to session. */
        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        /* If last connection failed don't display authorization link. */
        switch ($connection->http_code) {
            case 200:
                /* Build authorize URL and redirect user to Twitter. */
                $url = $connection->getAuthorizeURL($token);
                header('Location: ' . $url);
                break;
            default:
                /* Show notification if something went wrong. */
                echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }

    }

    public function callback(){
        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            $_SESSION['oauth_status'] = 'oldtoken';
            $loc = (string)$this->config->clear_session_url;
            header('Location: '.$loc);
        }

        /* Create twitteroauth object with app key/secret and token key/secret from default phase */
        $connection = new twitteroauth(
            (string)$this->config->consumer_key,
            (string)$this->config->consumer_secret,
            $_SESSION['oauth_token'],
            $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;

        // get user
        $tuser = $connection->get("account/verify_credentials");

        /* Remove no longer needed request tokens */
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->http_code) {
            /* The user has been verified and the access tokens can be saved for future use */
            $_SESSION['status'] = 'verified';
           // echo "user is ".(string) new xo_string($tuser);
            // does user already exist?
            $user = new user("user_twitter_id='$tuser->id'");
            if ( $user->exists){
                // set logged in
                $method = (string)$this->config->user_login_method;
                $user->$method();
                // go back to after login screen
                $loc = (string)$this->config->logged_in_redirect;
                header("Location: $loc");
                return true;
            } else {
                // create new user
                $user = new user();
                $user->login_type = 'twitter';
                $user->username = $tuser->screen_name;
                $user->user_twitter_id = $tuser->id;
                $user->save();
                // redirect
                $loc = (string)$this->config->new_user_redirect;
                header("Location: $loc/$user->id");
                return true;
            }
        } else {
            echo $connection->http_code;
            echo new xo_array($connection->http_info);


            /* Save HTTP status for error dialog on connnect page.
            $loc = (string)$this->config->clear_session_url;
            header('Location: '.$loc);
            return;
            */
        }
        echo "done";


    }
}
