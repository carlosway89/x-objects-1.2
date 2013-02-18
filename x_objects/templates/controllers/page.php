<?php
class page_controller extends xo_controller {
	public function e404(){
		global $container;
		$this->layout = "default";
		$this->render("e404");
	}
}
?>
