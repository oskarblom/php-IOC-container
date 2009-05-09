<?php

class CustomerServiceImpl implements CustomerService {
	
	/**
	 * @var EmployeeService
	 */
	private $employeeService;
	
	
	public function __construct(EmployeeService $employeeService) {
		$this->employeeService = $employeeService;
	}
	
	public function getNameAndDepartment() {
		return $this->employeeService->getInfo();
	}
}
?>