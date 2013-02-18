<?php
class xml_object extends RealXML {

	//! magic get
	public function __get($what){
		return parent::xml()->$what;
	}

}
?>