<?php

namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\listener\EntityChangeEvent;
use n2n\persistence\orm\Entity;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\script\entity\listener\impl\ScriptListenerAdapter;

class MappedDeleteScriptListener extends ScriptListenerAdapter {
	private $many;
	private $targetMany;
	private $propertyAccessProxy;
	private $targetPropertyAccessProxy;
	
	public function __construct(PropertyAccessProxy $propertyAccessProxy, 
			PropertyAccessProxy $targetPropertyAccessProxy, $many, $targetMany) {
		$this->propertyAccessProxy = $propertyAccessProxy;
		$this->targetPropertyAccessProxy = $targetPropertyAccessProxy;
		$this->many = $many;
		$this->targetMany = $targetMany;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\listener\ScriptListener::onEntityChanged()
	 */
	public function onEntityChanged(EntityChangeEvent $event) {
		if ($event->getType() != EntityChangeEvent::TYPE_ON_DELETE) return;
		
		$entity = $event->getEntity();
		$value = $this->propertyAccessProxy->getValue($entity);
		if ($this->targetMany) {
			foreach ($value as $targetEntity) {
				$this->process($entity, $targetEntity);
			}
		} else if ($value !== null) {
			$this->process($entity, $value);
		}
	}
	
	private function process(Entity $entity, Entity $targetEntity) {
		if (!$this->many) {
			if ($entity === $this->targetPropertyAccessProxy->getValue($targetEntity)) {
				$this->targetPropertyAccessProxy->setValue($targetEntity, null);
			}
			return;
		}
		
		$targetValues = $this->targetPropertyAccessProxy->getValue($targetEntity);
		if ($targetValues === null) return;
		
		foreach ($targetValues->getArrayCopy() as $key => $targetValue) {
			if ($targetValue === $entity) {
				unset($targetValues[$key]);
			}
		}
		$this->targetPropertyAccessProxy->setValue($targetEntity, $targetValues);
	}

}