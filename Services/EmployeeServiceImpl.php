<?php
class EmployeeServiceImpl implements EmployeeService {

	private $employeeName = 'oskar';
	
	/**
	 * @var DepartmentService
	 */
	private $departmentService;
	
	public function setDepartmentService($departmentService) {
		$this->departmentService = $departmentService;
	}
	
	function getInfo() {
		return ' Employee name: ' . $this->employeeName . 
		', Department name: ' . $this->departmentService->getDepartmentName() 
		. ".\n";
	}
	
	function getEmployeeName() {
		return $this->employeeName;
	}
}
?>