<?php
class home_controller extends xo_controller {
	public function default_action(){
		global $container;
		$this->layout = "default";
		$this->render('home');
	}
	
}
?>
