<?php
//! representation of a message recipient
interface Recipient_interface {

	//! type of recipient
	const TYPE_INTERNAL = 1;
	const TYPE_EMAIL = 2;
	
	//! get or set type
	public function type( $type = null );
	
	//! get or set the address / destination
	public function destination ( $destination = null );

}