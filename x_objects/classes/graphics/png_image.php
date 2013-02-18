<?php

//! representation of a png image
class png_image extends gd_image {

	//! construct
	public function __construct( $width, $height, $bkg_color) {
	
		//echo get_class() . "<br>";
	
		// set width and height
		$this->width = $width;
		$this->height = $height;
	
		//! construct parent
		parent::__construct( "png" );
	
		// set bkg color
		$this->set_color( "background", $bkg_color);
	
	}

}

?>