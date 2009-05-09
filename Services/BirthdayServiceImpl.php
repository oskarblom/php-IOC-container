<?php
class BirthdayServiceImpl implements BirthdayService{
	/**
	 * 
	 * @var Child
	 */
	private $child;
	
	public function setChild(Child $child) {
		$this->child = $child;
	}
	
	public function birthday() {
		print 'the child with name '. $this->child->getName() . ' received '. 
			implode(' and ', $this->child->gift());
	}
}
?>