<?php

//! representation of a file upload
class file_upload extends AttributedObject {

	//! file extensions
	private static $extensions = array (
	
		"image" => array ( "jpeg", "JPEG" , "jpg", "JPG", "png", "PNG" , "gif", "GIF" ),
		"video" => array ( "mpeg", "MPEG" , "mpg", "MPG", "swf", "SWF" , "avi", "AVI" )
	
	);

	//! create a new one
	public function __construct( $name, $new_name = null ) {
		global $container;
		// save name and target
		if ( isset( $_FILES[$name]['name'] )) {
			$this->name = $name;
			$this->extension = substr(strrchr($_FILES[$name]['name'],'.'),1);
			$this->new_name = ( $new_name ) ? $new_name : $container->services->utilities->random_password( 10 ) . "." . $this->extension;
			
		} else {
			echo "no such file uploaded";
		}
		
	}
	
	//! move file to its new location
	public function move_to( $target ) {
	
		$new_file = $target.$this->new_name;
		if( ! @move_uploaded_file($_FILES[$this->name]['tmp_name'], $new_file)) {
			$this->error = "Unable to create file $new_file";
			return false;
		} else return true;
	}

	// magic get
	public function __get( $what ) {
	
		switch ( $what ) {
		
			// does the file actually exist?
			case 'exists':
			
				return isset( $_FILES[$this->name]['tmp_name']) && file_exists( $_FILES[$this->name]['tmp_name']);
			break;
		
			case 'is_image':
			
				return in_array( $this->extension, self::$extensions['image'] );
				
			break;
			
			case 'is_video':
			
				return in_array( $this->extension, self::$extensions['video'] );
				
			break;
			
			
			default:
			
				return parent::__get( $what );
				
			break;
			
		
		}
	
	}

}

?>