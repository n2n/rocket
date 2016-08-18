<?php

namespace rocket\script\core;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\core\IllegalStateException;

class CompatibilityTest {
	private $entityProperty;
	private $propertyAccessProxy;
	private $failed = false;
	private $exception = null;
	
	
	public function __construct(EntityProperty $entityProperty = null, 
			PropertyAccessProxy $propertyAccessProxy = null) {
		$this->entityProperty = $entityProperty;
		$this->propertyAccessProxy = $propertyAccessProxy;
	}
	/**
	 * @return EntityProperty
	 */
	public function getEntityProperty() {
		return $this->entityProperty;
	}
	/**
	 * @return PropertyAccessProxy
	 */
	public function getPropertyAccessProxy() {
		return $this->propertyAccessProxy;
	}
	
	public function entityPropertyTestFailed($reason = null) {
		if ($this->entityProperty === null) {
			throw new IllegalStateException('No EntityProperty available');
		}
		
		$this->failed = true;
		$this->exception = new CompatibilityTestFailedException(
				'ScriptField is not compatible with EntityProperty ' . $this->entityProperty->getName() 
						. ($reason ? ' Reason: ' . $reason : ''));
	}
	
	public function propertyTestFailed($reason = null) {
		if ($this->propertyAccessProxy === null) {
			throw new IllegalStateException('No PropertyAccessProy available');
		}
		
		$this->failed = true;
		$this->exception = new CompatibilityTestFailedException(
				'ScriptField is not compatible with Property ' . $this->propertyAccessProxy->getPropertyName() 
						. ($reason ? ' Reason: ' . $reason : ''));
	}
	
	public function getException() {
		return $this->exception;
	}
	
	public function hasFailed() {
		return $this->failed;	
	}
}

class CompatibilityTestFailedException extends ScriptException {
	
}