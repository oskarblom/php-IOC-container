<?php
require_once 'IOC/XMLApplicationContext.php';
require_once 'IOC/WebApplicationContext.php';
require_once 'IOC/WebApplicationContextUtils.php';
require_once 'Services/CustomerService.php';
require_once 'Services/CustomerServiceImpl.php';
require_once 'Services/DepartmentService.php';
require_once 'Services/DepartmentServiceImpl.php';
require_once 'Services/EmployeeService.php';
require_once 'Services/EmployeeServiceImpl.php';
require_once 'Services/BirthdayService.php';
require_once 'Services/BirthdayServiceImpl.php';
require_once 'Services/Child.php';
require_once 'Services/MaleChild.php';
require_once 'Services/FemaleChild.php';
require_once 'Services/UnbornChild.php';

try {
	$context = WebApplicationContextUtils::getContext('applicationContext.xml');
	/* @var $customerService CustomerService */
	$customerService = $context->getObject('customerService');
	print $customerService->getNameAndDepartment();
	
	print "**********************************************\n";	
	
	// @var $birthdayService BirthdayService */
	$birthdayService = $context->getObject('birthdayService');
	$birthdayService->birthday();

} catch (Exception $e) {
	print $e->getMessage();
}
print "\n";





?>
