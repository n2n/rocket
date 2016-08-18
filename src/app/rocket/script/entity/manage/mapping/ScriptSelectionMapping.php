<?php
namespace rocket\script\entity\manage\mapping;

use rocket\script\entity\manage\security\SelectionPrivilegeConstraint;
use rocket\script\entity\command\ScriptCommand;
use n2n\core\Message;
use n2n\core\UnsupportedOperationException;
use rocket\script\entity\EntityScript;
use rocket\script\entity\manage\ScriptSelection;
use n2n\reflection\property\ValueIncompatibleWithConstraintsException;
use rocket\script\entity\manage\security\MappingArrayAccess;

class ScriptSelectionMapping {
	private $contextEntityScript;
	private $mappingDefinition;
	private $scriptSelection;
	private $scriptSelectionPrivilegeConstraint;
	private $accessRestrictors = array();
	private $values = array();
	private $parentMapping;
	private $relatedMappings = array();
	private $orgValues = array();
	private $validators = array();
	private $constraints = array();
	private $listeners = array();
	private $listenerBindings = array();
	
	public function __construct(EntityScript $contextEntityScript, MappingDefinition $mappingDefinition, 
			ScriptSelection $scriptSelection,
			SelectionPrivilegeConstraint $scriptSelectionPrivilegeConstraint = null) {
		$this->contextEntityScript = $contextEntityScript;
		$this->mappingDefinition = $mappingDefinition;
		$this->scriptSelection = $scriptSelection;
		$this->scriptSelectionPrivilegeConstraint = $scriptSelectionPrivilegeConstraint;
	}
	
	public function getContextEntityScript() {
		return $this->contextEntityScript;
	}
	
	public function determineEntityScript() {
		return $this->contextEntityScript->determineAdequateEntityScript(new \ReflectionClass($this->scriptSelection->getEntity()));
	}
	
	public function getMappingDefinition() {
		return $this->mappingDefinition;
	}
	/**
	 * @return \rocket\script\entity\manage\ScriptSelection
	 */
	public function getScriptSelection() {
		return $this->scriptSelection;
	}
	
	public function getSelectionPrivilegeConstraint() {
		return $this->scriptSelectionPrivilegeConstraint;
	}
	
	public function setSelectionPrivilegeConstraint(SelectionPrivilegeConstraint $scriptSelectionPrivilegeConstraint = null) {
		$this->scriptSelectionPrivilegeConstraint = $scriptSelectionPrivilegeConstraint;
	}
	
	public function registerAccessRestrictor(AccessRestrictor $accessRestrictor) {
		$this->accessRestrictors[spl_object_hash($accessRestrictor)] = $accessRestrictor;
	}
	
	public function unregisterAccessRestrictor(AccessRestrictor $accessRestrictor) {
		unset($this->accessRestrictors[spl_object_hash($accessRestrictor)]);
	}
	
	public function getAccessRestrictors() {
		return $this->accessRestrictors;
	}
	
	public function isAccessableBy(ScriptCommand $scriptCommand, $privilegeExt = null) {
		foreach ($this->accessRestrictors as $accessRestrictor) {
			if (!$accessRestrictor->isAccessableBy($scriptCommand, $privilegeExt)) {
				return false;
			}
		}
		
		return $this->scriptSelectionPrivilegeConstraint === null 
				|| $this->scriptSelectionPrivilegeConstraint->containsAccessablePrivilege($scriptCommand, $privilegeExt);
	}
	
	public function getValue($id) {
		if (array_key_exists($id, $this->values)) {
			return $this->values[$id];
		}
		
		return $this->getOrgValue($id);
	}
	
	private function createSetOperationException($id, $code, \Exception $previous) {
		throw new MappingOperationFailedException('Could not set value for field \'' . $id 
				. '\' on EntityScript \'' . $this->determineEntityScript()->getId() . '\'. Reason: ' . $previous->getMessage(), 0, $previous);
	}
	
	public function setValue($id, $value) {
		try {
			$this->mappingDefinition->getMappableById($id)
					->validateValue($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			$this->createSetOperationException($id, 0, $e);
		} catch (MappingOperationFailedExcpetion $e) {
			$this->createSetOperationException($id, 0, $e);
		} 
		
		$this->values[$id] = $value;
	}
	
	public function setValues(array $values) {
		foreach ($this->values as $id => $value) {
			$this->setValue($id, $value);
		}
	}
	
	public function getValues() {
		return $this->values;
	}
	
	public function getAllValues() {
		return $this->values + $this->getOrgValues();
	}
	
	public function getOrgValues() {
		foreach ($this->mappingDefinition->getMappables() as $id => $mappable) {
			if (array_key_exists($id, $this->orgValues)) continue;
			
			$this->orgValues[$id] = $this->readOrgValue($mappable);
		}
		
		return $this->orgValues;
	}
		
	public function getOrgValue($id) {
		if (!array_key_exists($id, $this->orgValues)) {
			try {
				$this->orgValues[$id] = $this->readOrgValue($this->mappingDefinition->getMappableById($id));
			} catch (MappingOperationFailedExcpetion $e) {
				throw new MappingOperationFailedException('Could not read value for field \'' . $id 
						. '\' on EntityScript \'' . $this->determineEntityScript()->getId() . '\'. Reason: ' . $e->getMesage(), 0, $e);
			}
		}
		
		return $this->orgValues[$id];	
	}
	
	private function readOrgValue(Mappable $mappable) {
		if ($this->scriptSelection->hasTranslation() && $mappable->isTranslatable()) {
			return $mappable->translationRead($this->scriptSelection->getTranslation());
		} 
		
		if ($this->scriptSelection->hasDraft() && $mappable->isDraftable()) {
			return $mappable->draftRead($this->scriptSelection->getDraft());
		}
		
		return $mappable->read($this->scriptSelection->getEntity());
	}
	
	protected function setParentMapping(ScriptSelectionMapping $parentMapping) {
		$this->parentMapping = $parentMapping;
	}
	
	public function getParentMapping() {
		return $this->parentMapping;
	}
	
	public function registerRelatedMapping($id, ScriptSelectionMapping $relatedMapping) {
		if (!isset($this->relatedMappings[$id])) {
			$this->relatedMappings[$id] = array();
		}
		
		$this->relatedMappings[$id][] = $relatedMapping;
		$relatedMapping->setParentMapping($this);
		
// 		$this->registerListener(
// 				new OnValidateMappingListener(function(MappingValidationResult $mappingValidationResult) use ($id, $relatedMapping) {
// 					$relatedMapping->validate($mappingValidationResult->createValueResult($id));
// 				}));
			
// 		$this->registerListener(
// 				new OnWriteMappingListener(function() use ($relatedMapping) {
// 					$relatedMapping->write();
// 				}));
	}
	
	public function unregisterRelatedMappings($id) {
		foreach ($this->getRelatedMappings($id) as $relatedMapping) {
			$relatedMapping->setParentMapping(null);
		}
		$this->relatedMappings[$id] = array();
	}
	
	public function hasRelatedMapping($id) {
		return isset($this->relatedMappings[$id]) && sizeof($this->relatedMappings[$id]);
	}
	
	public function getRelatedMapping($id) {
		if (!$this->hasRelatedMapping($id)) {
			throw new UnsupportedOperationException('No related mappings for ' . $id . ' available.');
		}
		
		if (1 < sizeof($this->relatedMappings[$id])) {
			throw new UnsupportedOperationException('More then one related mappings for ' . $id . ' available.');
		}
		
		return current($this->relatedMappings[$id]); 
	}
	
	public function getRelatedMappings($id) {
		if (!$this->hasRelatedMapping($id)) {
			return array();
		}
		
		return $this->relatedMappings[$id];
	}
	
	/**
	 * Only call this method if this is a root ScriptSelectionMapping
	 * @param MappingValidationResult $mappingValidationResult
	 * @return boolean
	 */
	public function save(MappingValidationResult $mappingValidationResult) {
		$this->validate($mappingValidationResult);
		if ($mappingValidationResult->hasFailed()) return false;
		$this->write();
		$this->flush();
		return true;
	}
	
	private function validate(MappingValidationResult $mappingValidationResult) {
		foreach ($this->listeners as $listener) {
			$listener->onValidate($mappingValidationResult, $this);
		}
				
		foreach ($this->relatedMappings as $id => $relatedMappings) {
			foreach ($relatedMappings as $relatedMapping) {
				$relatedMapping->validate($mappingValidationResult);
			}
		}
		
		foreach ($this->validators as $validator) {
			$validator->validate($mappingValidationResult, $this);
		}
		
		if ($this->scriptSelectionPrivilegeConstraint !== null) {
			$this->scriptSelectionPrivilegeConstraint->validateValues(
					new MappingArrayAccess($this, false), $mappingValidationResult);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->validated($mappingValidationResult, $this);
		}
	}
	
	private function write() {
		foreach ($this->listeners as $listener) {
			$listener->onWrite($this);
		}
		
		foreach ($this->relatedMappings as $id => $relatedMappings) {
			foreach ($relatedMappings as $relatedMapping) {
				$relatedMapping->write();
			}
		}
		
		if ($this->scriptSelection->hasTranslation()) {
			$this->mappingDefinition->translationWriteAll($this->scriptSelection->getTranslation(), $this->values);
		} else if ($this->scriptSelection->hasDraft()) {
			$this->mappingDefinition->draftWriteAll($this->scriptSelection->getDraft(), $this->values);
		} else {			
			$this->mappingDefinition->writeAll($this->scriptSelection->getOriginalEntity(), $this->values);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->written($this);
		}
	}
	
	private function flush() {


		foreach ($this->relatedMappings as $id => $relatedMappings) {
			foreach ($relatedMappings as $relatedMapping) {
				$relatedMapping->flush();
			}
		}
		
		foreach ($this->listeners as $listener) {
			$listener->flush($this);
		}
	}
	
	public function copy(ScriptSelectionMapping $targetMapping) {
		$targetMappingDefinition = $targetMapping->getMappingDefinition();
		$targetType = $targetMapping->getScriptSelection()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingDefinition->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
	}
	
	
	
// 	public function draftCopy(ScriptSelectionMapping $entityMapping) {
// 		foreach ($entityMapping->getWritables() as $id => $draftable) {
// 			if (isset($this->draftables[$id])) {
// 				$entityMapping->setValue($id, $this->draftables[$id]->draftCopy($this->getValue($id)));
// 			}
// 		}
// 	}
	
// 	public function translationCopy(ScriptSelectionMapping $entityMapping) {
		
// 	}
	
	
	public function registerListener(ScriptSelectionMappingListener $listener, $relatedFieldId = null) {
		$objectHash = spl_object_hash($listener);
		$this->listeners[$objectHash] = $listener;
		if (!isset($this->listenerBindings[$relatedFieldId])) {	
			$this->listenerBindings[$relatedFieldId][$objectHash] = $listener;
		}
	}
	
	public function getFieldRelatedListeners($fieldId) {
		if (isset($this->listenerBindings[$fieldId])) {
			return $this->listenerBindings[$fieldId];
		}
		
		return array();
	}
	
	public function unregisterListener(ScriptSelectionMappingListener $listener) {
		$objectHash = spl_object_hash($listener);
		unset($this->listeners[$objectHash]);
		foreach ($this->listenerBindings as $fieldId => $listeners) {
			unset($this->listenerBindings[$fieldId][$objectHash]);
		}
	}
	
	public function unregisterFieldRelatedListeners($fieldId) {
		unset($this->listenerBindings[$fieldId]);
	}
	
	public function registerValidator(MappingValidator $validator) {
		$this->validators[spl_object_hash($validator)] = $validator;
	}
	
	public function registerConstraint(MappingConstraint $mappingConstraint) {
		$this->registerValidator($mappingConstraint);
		$this->constraints[spl_object_hash($mappingConstraint)] = $mappingConstraint;
	}
	
	public function unregisterValidator(MappingValidator $validator) {
		unset($this->validators[spl_object_hash($validator)]);
		unset($this->constraints[spl_object_hash($validator)]);
	}
	
	public function acceptsValue($id, $value) {
		foreach ($this->constraints as $constraint) {
			if (!$constraint->acceptsValue($id, $value)) return false;
		}
		return true;
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptSelectionMapping && $this->determineEntityScript()->equals($obj->determineEntityScript())
				&& $this->scriptSelection->equals($obj->getScriptSelection());
	}
}

interface ScriptSelectionMappingListener {
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping);
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping);
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping);
	public function written(ScriptSelectionMapping $scriptSelectionMapping);
	public function flush(ScriptSelectionMapping $scriptSelectionMapping);
}

class OnWriteMappingListener implements ScriptSelectionMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onValidate()
	 */
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::validated()
	 */
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onWrite()
	 */
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping) {
		$this->closure->__invoke($scriptSelectionMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::written()
	 */
	public function written(ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function flush(ScriptSelectionMapping $scriptSelectionMapping) {}

}

class WrittenMappingListener implements ScriptSelectionMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onValidate()
	 */
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::validated()
	 */
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onWrite()
	 */
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::written()
	 */
	public function written(ScriptSelectionMapping $scriptSelectionMapping) {
		$this->closure->__invoke($scriptSelectionMapping);
	}
	
	public function flush(ScriptSelectionMapping $scriptSelectionMapping) {}
}

class OnValidateMappingListener implements ScriptSelectionMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onValidate()
	 */
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { 
		$this->closure->__invoke($mappingValidationResult, $scriptSelectionMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::validated()
	 */
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onWrite()
	 */
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::written()
	 */
	public function written(ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function flush(ScriptSelectionMapping $scriptSelectionMapping) {}
}

class ValidatedMappingListener implements ScriptSelectionMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onValidate()
	 */
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::validated()
	 */
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) { 
		$this->closure->__invoke($mappingValidationResult, $scriptSelectionMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onWrite()
	 */
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::written()
	 */
	public function written(ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function flush(ScriptSelectionMapping $scriptSelectionMapping) {}
}

class FlushMappingListener implements ScriptSelectionMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onValidate()
	 */
	public function onValidate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::validated()
	 */
	public function validated(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::onWrite()
	 */
	public function onWrite(ScriptSelectionMapping $scriptSelectionMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\ScriptSelectionMappingListener::written()
	 */
	public function written(ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function flush(ScriptSelectionMapping $scriptSelectionMapping) {
		$this->closure->__invoke($scriptSelectionMapping);
	}
}
class SimpleMappingValidator implements MappingValidator {
	private $closure;

	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}

	public function validate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {
		$this->closure->__invoke($mappingValidationResult, $scriptSelectionMapping);
	}
}


class MappingValidationResult {
	private $messages;
	
	public function hasFailed() {
		return 0 < sizeof($this->messages);
	}
	
	public function isValid() {
		return empty($this->messages);
	}
		
	public function addError($id, Message $message) {
		$this->messages[] = $message;
	}
	
	public function getMessages() {
		return $this->messages;
	}
}



