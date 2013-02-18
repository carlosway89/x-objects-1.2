<?php
// deny access if not privileged
if ( ! $container->logged_in )
	$container->redirect( $webroot . "dologin");
elseif (! $container->me->is_admin ) {
	echo "not authorized";
	$bypass_view = true;
} else {

// set logged in class
$logged_in_class = $container->logged_in() ? "logged-in" : "not-logged-in";
// auth key for login api
$auth_key = "sp_user";
// get request
$req = new REQUEST();
// get services
$services = $container->managed_services;
// by default don't show pagination
$show_pagination = $services_visible = "hidden";
// if we have a type
if ( $req->type ) {
	$service = $services[$req->type];
	// in this case, show pagination
	$show_pagination = $services_visible = "visible";
	// set page max
	$page_max = 10;
	// get the paginator
	$paginator = new paginator( "pagination",$service->key,$page_max,"10 from page 1",1);
}


}
?>