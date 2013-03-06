<?php
class home_controller extends xo_controller {
	public function default_action(){
		global $container;
		$this->layout = "skeleton";
		$this->render('home');
	}
	
}
?>
