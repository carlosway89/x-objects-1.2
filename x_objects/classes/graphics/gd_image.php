<?php

//! a general image rendered with GD
abstract class gd_image extends magic_object {

	//! the image resource
	protected $resource = null;
	
	//! background color
	protected $bkg_color = null;
	
	//! colors
	protected $colors = array();

	//! construct
	public function __construct( $content_type ) {
	
		$this->type = $content_type;
	
		// set header type
		header("content-type: image/$content_type");
 
		// set resources
		$this->resource = imagecreate($this->width, $this->height);
 
	}
	
	//! set a color
	public function set_color( $type, $color ) {
	
		// color translation
		$colormap = array( "black" => array(0,0,0) );
	
		switch ( $type ) {
		
			case 'background':
			
				$this->bkg_color = imagecolorallocate($this->resource, $colormap[$color][0], $colormap[$color][1], $colormap[$color][2]);

			break;
				
		
		}
		
		// set standard colors
		$this->colors["white"] = imagecolorallocate($this->resource, 255, 255, 255);
		$this->colors["red"] = imagecolorallocate($this->resource, 255, 0, 0);
		$this->colors["green"] =  imagecolorallocate($this->resource, 0, 255, 0);
		$this->colors["blue"] =  imagecolorallocate($this->resource, 0, 0, 255);
	
	
	}
	
	//! draw a line
	public function line( $x1, $y1, $x2, $y2, $color ) {
	
		imageline( $this->resource,$x1,$y1,$x2,$y2,$this->colors[$color]);
	}
	
	//! draw some text
	public function text( $size, $angle, $x, $y, $color, $ttf, $text) {
				
		imagettftext( $this->resource,$size,$angle,$x,$y,$this->colors[$color],$ttf,$text);

	}
	
	//! draw a rectangle
	public function rect( $type, $x1,$y1,$x2,$y2,$color) {
	
		if ( $type == "filled" )
			imagefilledrectangle( $this->resource, $x1,$y1,$x2,$y2,$this->colors[$color]);
		else
			imagerectangle( $this->resource, $x1,$y1,$x2,$y2,$this->colors[$color]);
	
	}
	
	//! render the image
	public function render() {
	
		//save the image as a png and output 
		if ( $this->type == "png")
			imagepng($this->resource );
 
		//Clear up memory used
		imagedestroy($this->resource);

	}
	
	//! draw an arrow
	public function arrow( $type = "filled" , $x1, $y1, $x2, $y2, $alength, $awidth, $color) {
	
		$distance = sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));

		$dx = $x2 + ($x1 - $x2) * $alength / $distance;
		$dy = $y2 + ($y1 - $y2) * $alength / $distance;

		$k = $awidth / $alength;

		$x2o = $x2 - $dx;
		$y2o = $dy - $y2;

		$x3 = $y2o * $k + $dx;
		$y3 = $x2o * $k + $dy;

		$x4 = $dx - $y2o * $k;
		$y4 = $dy - $x2o * $k;

		imageline($this->resource, $x1, $y1, $dx, $dy, $this->colors[$color]);
		if ( $type == "filled" )
			imagefilledpolygon($this->resource, array($x2, $y2, $x3, $y3, $x4, $y4), 3, $this->colors[$color]);
		else
			imagepolygon($this->resource, array($x2, $y2, $x3, $y3, $x4, $y4), 3, $this->colors[$color]);
	}

}

?>