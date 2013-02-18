<?php
/* Start session and load library. */
session_start();

// bootstrap x-objects
require_once( "../../include/bootstrap.php");

// get container
$container = x_objects::instance();

// get twitter configuration
$config = $container->app->xml->twitter;

/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth((string)$config->consumer_key, (string)$config->consumer_secret);
 
/* Get temporary credentials. */
$request_token = $connection->getRequestToken((string)	$config->oauth_callback);

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
