<?php
class MaleChild implements Child {
	
	private $name = 'baader';
	
	public function getName() {
		return $this->name; 
	}
	
	
	public function gift() {
		return array('meat', 'stick');
	}
}
?>