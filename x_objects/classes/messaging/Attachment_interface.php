<?php

//! public interface for message attachments
interface Attachment_interface {

	//! type of attachments
	const TYPE_IMAGE = 1;
	
	//! get the image uri
	public function getURI();
	
	//! delete the attachment
	public function delete();
}