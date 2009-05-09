<?php
class FemaleChild implements Child {
	
	private $name = 'meinhof';
	
	public function getName() {
		return $this->name; 
	}
	
	public function gift() {
		return array('birthcontrol', 'doll');
	}
}
?>