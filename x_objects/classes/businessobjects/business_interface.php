<?php

//! interface for business objects
interface business_interface {

	//! retrieve datasource from static context
	public static function source();

	//! return object as well-formed html
	public function html( $view );
}
?>