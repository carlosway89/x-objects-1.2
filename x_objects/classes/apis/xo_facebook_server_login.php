<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 23/01/13
 * Time: 12:10 AM
 */
class xo_facebook_server_login {
    private $uri, $ses,$req,$user,$config;
    public function __construct(){
        $this->uri = new REQUEST_URI();
        $this->ses = new SESSION();
        $this->req = new REQUEST();
    }
    public function go(){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        // get configuration
        $this->config = $container->config->facebook;
        // check if we got code from facebook
        // if not, redirect
        if(! $this->req->code) {
            $this->ses->state =  md5(uniqid(rand(), TRUE)); // CSRF protection
            header("Location: "."https://www.facebook.com/dialog/oauth?client_id="
                    . (string)$container->config->facebook->id
                    . "&redirect_uri=" . urlencode((string)$container->config->facebook->url)
                    . "&state=". $this->ses->state

            );
            // we have a code, so we are authorized
        } else {
            $this->user = null;
            // avoid CSRF
            if  ($this->ses->state && ($this->ses->state === $this->req->state)) {
                $token_url = "https://graph.facebook.com/oauth/access_token?"
                    . "client_id=" . $container->config->facebook->id
                    . "&redirect_uri=" . urlencode($container->config->facebook->url)
                    . "&client_secret=" . $container->config->facebook->secret
                    . "&code=" . $this->req->code;

                $response = file_get_contents($token_url);
                $params = null;
                parse_str($response, $params);
                $this->ses->access_token = $params['access_token'];
                $graph_url = "https://graph.facebook.com/me?access_token="
                    . $this->ses->access_token;
                $this->user = json_decode(file_get_contents($graph_url));
               // echo (string) new xo_string($this->user);
                // find local user
                $username = $this->user->username? $this->user->username: "fbuser-".(string)new random_password(5);
                if ( strlen($username)>20) $username = substr($username,0,20);
                $me = new user("user_facebook_id='".$this->user->id."'");
                if ( $me->exists){
                    $method = (string)$this->config->user_login_method;
                    // now login me in
                    $me->$method();
                    $loc = (string)$this->config->logged_in_redirect;
                    header("Location: $loc");
                    return;
                } else {
                    // need to add to database
                    $me = new user();
                    $me->login_type = 'facebook';
                    $username = $this->user->username? $this->user->username: "fbuser-".(string)new random_password(5);
                    $me->username = $username;
                    $me->user_facebook_id = (string)$this->user->id;
                    $me->firstname = $this->user->first_name;
                    $me->lastname = $this->user->last_name;
                    $me->is_active = true;
                    $sr= $me->save();
                    if ( $sr){
                        $method = (string)$this->config->user_login_method;
                        $me->$method();
                        $loc = (string)$this->config->new_user_redirect;
                        header("Location: $loc");
                        return;
                    } else {
                        echo "failed to save me! $me->save_error (username: $username)<br>";
                    }
                }
            }

        }

    }
}
