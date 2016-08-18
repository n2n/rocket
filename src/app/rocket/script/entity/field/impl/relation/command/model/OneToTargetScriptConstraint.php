<?php

namespace rocket\script\entity\field\impl\relation\command\model;

use n2n\reflection\property\PropertyAccessProxy;
use rocket\script\entity\manage\ScriptState;
use n2n\persistence\orm\Entity;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\mapping\WrittenMappingListener;

class OneToTargetScriptModificator extends ScriptModificatorAdapter {
	private $targetScriptState;
	private $targetEditableId;
	private $propertyAccessProxy;

	public function __construct(ScriptState $targetScriptState, Entity $entity, $toMany) {
		$this->targetScriptState = $targetScriptState;
		$this->entity = $entity;
		$this->toMany = (boolean)$toMany;
	}
	
	public function setTargetEditableId($targetEditableId) {
		$this->targetEditableId = $targetEditableId;
	}
	
	public function getTargetEditableId() {
		return $this->targetEditableId;
	}
	
	public function setPropertyAccessProxy(PropertyAccessProxy $propertyAccessProxy = null) {
		$this->propertyAccessProxy = $propertyAccessProxy;
	}
	
	public function getPropertyAccessProxy() {
		return $this->propertyAccessProxy;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		if ($this->targetScriptState !== $scriptState
				|| !$scriptSelectionMapping->getScriptSelection()->isNew()) return;

		if ($this->targetEditableId !== null) {
			$scriptSelectionMapping->setValue($this->targetEditableId, $this->entity);
		}

		if ($this->propertyAccessProxy !== null) {
			$that = $this;
			if (!$this->toMany) {
				$scriptSelectionMapping->registerListener(new WrittenMappingListener(
						function () use ($that, $scriptSelectionMapping) {
							$that->propertyAccessProxy->setValue($that->entity, $scriptSelectionMapping->getScriptSelection()->getEntity());
						}));
			} else {
				$scriptSelectionMapping->registerListener(new WrittenMappingListener(
						function () use ($that, $scriptSelectionMapping) {
							$targetEntities = $that->propertyAccessProxy->getValue($that->entity);
							if ($targetEntities === null) {
								$targetEntities = new \ArrayObject();
							}
							$targetEntities[] = $scriptSelectionMapping->getScriptSelection()->getEntity();
							$that->propertyAccessProxy->setValue($that->entity, $targetEntities);
						}));
			}
		}
	}
}