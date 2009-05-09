<?php
require_once 'IOC/ApplicationContext.php';

/**
 * This class differ's from the WebApplicationContext in that it
 * looks up and resolves all necessary dependencies for the required object
 * only. This will be done for every call to XMLApplicationContext::getObject($name)
 * @author oskarb
 *
 */
class XMLApplicationContext implements ApplicationContext
{
	/**
	 * 
	 * @var String
	 */
	private $xmlFileName;
	
	/**
	 * 
	 * @var SimpleXMLElement
	 */
	private $xmlElement;
	
	/**
	 * 
	 * @param $xmlFileName
	 * @return void
	 */
	public function __construct($xmlFileName) {
		$this->xmlFileName = $xmlFileName;
		$this->init();
	}
	
	/**
	 * Sets up the SimpleXMLElement
	 * @return void
	 */
	private function init() {
		$xmlObject = simplexml_load_file($this->xmlFileName);
		if(!$xmlObject instanceof SimpleXMLElement) {
			throw new Exception("Can't read XML {$this->xmlFileName}". 
				"Either it's missing or malformed");
		}
		$this->xmlElement = $xmlObject;
	}
	
	/**
	 * Public method. Wraps the recursion
	 * @param $name 
	 * @return object
	 */
	public function getObject($name) {
		return $this->resolveObject($name);
	}
	
	
	/**
	 * Returns an instance with all dependecies injected
	 * @param $name
	 * @return object
	 */
	private function resolveObject($name) {
		$result = $this->xmlElement->xpath(
			"/objects/object[@name = '$name']");
		if(!is_array($result) || count($result) == 0) {
			throw new Exception("No object with name $name defined");
		}
		if(count($result) > 1) {
			throw new Exception("Ambiguous reference to $name");
		}
		/* @var $resultObject SimpleXMLElement */
		$resultObject = $result[0];
		
		/* @var $reflectionClass ReflectionClass */
		$reflectionClass = $this->getReflectionTypeFromXMLObject(
			$resultObject);
		
		
		$args = array();
		//OK, walk the ctor list and append the resolved args recursively to the list
		foreach($resultObject->{'constructor-arg'} as $ctorArg) {
			array_push($args, $this->resolveObject(
				(string)$ctorArg->attributes()->ref));
		}
				
		if(count($args) == 0) {
			$objectInstance = $reflectionClass->newInstance();	
		} else {
			$objectInstance = $reflectionClass->newInstanceArgs($args);
		}
		
		// Walk the properties and inject the resolved instances recursively
		/* @var $property SimpleXMLElement */
		foreach($resultObject->property as $property) {
			$propName = (string)$property->attributes()->name;
			$refName = (string)$property->attributes()->ref;
			$reflectionClass->getMethod('set' . ucfirst($propName))->invoke(
				$objectInstance, $this->resolveObject($refName));
		}
		return $objectInstance;
	}
	
	/**
	 * Get a ReflectionClass instance given a SimpleXMLElement
	 * @param $element
	 * @return ReflectionClass
	 */
	private function getReflectionTypeFromXMLObject(SimpleXMLElement $element) {		
		$result = (string)$element->attributes()->type;
		$reflectionClass = new ReflectionClass($result);
		return $reflectionClass;
	}
}


?>