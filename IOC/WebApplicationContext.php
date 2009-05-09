<?php
/**
 * 
 * @author oskarb
 *
 */

class WebApplicationContext implements ApplicationContext {
	
	/**
	 * 
	 * A list of all resolved dependecies for this application context
	 * @var array
	 */	
	private $resolvedDependecies = array();
	
	/**
	 * 
	 * The application context root node
	 * @var SimpleXMLElement
	 */
	private $xmlRootElement;
	
	/**
	 * 
	 * Constructor
	 * @return voiod
	 */
	public function __construct($xmlFileName) {
		$this->xmlFileName = $xmlFileName;
		$this->init();
	}
	
	/**
	 * 
	 * @param string the object name as defined in the application context
	 * @return object
	 * (non-PHPdoc)
	 * @see IOC/ApplicationContext#getObject($name)
	 */
	public function getObject($name) {
		return $this->resolvedDependecies[$name];
	}
	
	/**
	 * 
	 * @return array of all the resolved dependencies for this application context
	 */
	public function getObjects() {
		return $this->resolvedDependecies;
	}
	
	/**
	 * 
	 * Sets up the parser and initiates the DI
	 * @return void
	 */
	private function init() {
		$xmlObject = simplexml_load_file($this->xmlFileName);
		if(!$xmlObject instanceof SimpleXMLElement) {
			throw new Exception("Can't read XML {$this->xmlFileName}". 
				"Either it's missing or malformed");
		}
		$this->xmlRootElement = $xmlObject;
		$this->resolveObjects();
	}

	/**
	 * 
	 * Iterates over the applicationContext objects and injects dependecies
	 * recursively
	 * @return void
	 */
	private function resolveObjects() {
		$objects = $this->xmlRootElement->xpath("/objects/object");
		if(!is_array($objects) || count($objects) == 0) {
			throw new Exception("No object with name $name defined");
		}
		foreach($objects as $object) {
			$name = (string)$object->attributes()->name;
			if(!array_key_exists($name, $this->resolvedDependecies)) {
				$this->resolvedDependecies[$name] = $this->resolveObject($name);
			}
		}
	}
	
	/**
	 * 
	 * @param $name
	 * @return object
	 */
	private function resolveObject($name) {
		$currentObjectElement = $this->getObjectXMLElementByName($name);
		$reflectionClass = $this->getReflectionTypeFromXMLObject($currentObjectElement);
				
		$ctorArgs = $this->getResolvedCtorArgs($currentObjectElement->{'constructor-arg'});	
		$objectInstance = (count($ctorArgs) == 0) ? 
			$reflectionClass->newInstance() :
		  	$objectInstance = $reflectionClass->newInstanceArgs($ctorArgs);
		
		$propertyArgs = $this->getResolvedPropertyArgs($currentObjectElement->property);				
		$this->setProperties($reflectionClass, $propertyArgs, $objectInstance);
		
		return $objectInstance;
	}

	/**
	 * 
	 * @param string $name
	 * @return SimpleXMLElement
	 */
	private function getObjectXMLElementByName($name) {
		$currentObjectElementList = $this->xmlRootElement->xpath(
			"/objects/object[@name = '$name']");
		if(!is_array($currentObjectElementList) || count($currentObjectElementList) == 0) {
			throw new Exception("No object with name $name defined");
		}
		if(count($currentObjectElementList) > 1) {
			throw new Exception("Ambiguous reference to $name");
		}
		if(!$currentObjectElementList[0] instanceof SimpleXMLElement) {
			throw new Exception("Error getting XML object for name $name");
		}
		return $currentObjectElementList[0];
	}
	
	/**
	 * 
	 * @param SimpleXMLElement $element
	 * @return ReflectionClass
	 */
	private function getReflectionTypeFromXMLObject(SimpleXMLElement $element) {
		$className = (string)$element->attributes()->type;
		if(!class_exists($className, false)) {
			throw new Exception("Class with name: $classname could not be found.");
		}
		$reflectionClass = new ReflectionClass($className);
		return $reflectionClass;
	}
	
	/**
	 * 
	 * @param ReflectionClass $reflectionClass
	 * @param array $propertyArgs
	 * @param object $objectInstance
	 * @return void
	 */
	private function setProperties(ReflectionClass $reflectionClass, array $propertyArgs, $objectInstance) {
		foreach($propertyArgs as $propertyArg) {
			$setterName = 'set' . ucfirst($propertyArg['propName']);
			if(!$reflectionClass->hasMethod($setterName)) { 
				throw new Exception('No setter defined for property ' . $propertyArg['propname']);			
			}
			/* @var $reflectionMethod ReflectionMethod */
			$reflectionMethod = $reflectionClass->getMethod($setterName);
			$reflectionMethod->invoke($objectInstance, $propertyArg['propRef']);
		}
	}
	
	/**
	 * 
	 * @param SimpleXMLElement $ctorArgs
	 * @return array
	 */
	private function getResolvedCtorArgs(SimpleXMLElement $ctorArgs) {
		$args = array();
		foreach($ctorArgs as $ctorArgElement) {
			$refName = (string)$ctorArgElement->attributes()->ref;
			$refObjectResult = $this->xmlRootElement->xpath("/objects/object[@name = '$refName']");
			$refObjectName = (string)$refObjectResult[0]->attributes()->name;
			
			if(!array_key_exists($refObjectName, $this->resolvedDependecies)) {
				$this->resolvedDependecies[$refObjectName] = $this->resolveObject($refObjectName);
			} 
			array_push($args, $this->resolvedDependecies[$refObjectName]);
		}
		return $args;	
	}
	
	/**
	 * 
	 * @param SimpleXMLElement $propertyArgs
	 * @return array
	 */
	private function getResolvedPropertyArgs(SimpleXMLElement $propertyArgs) {
		$args = array();
		foreach($propertyArgs as $property) {
			$propName = (string)$property->attributes()->name;
			if(!array_key_exists($propName, $this->resolvedDependecies)) {
				$this->resolvedDependecies[$propName] = $this->resolveObject($propName);
			}
			array_push($args, array('propName' => $propName, 
				'propRef' => $this->resolvedDependecies[$propName]));
		}
		return $args;
	}
}
?>