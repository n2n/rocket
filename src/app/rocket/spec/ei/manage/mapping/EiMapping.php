<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\manage\mapping;

use n2n\l10n\Message;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\EiFieldPath;
use n2n\util\col\HashSet;
use n2n\util\col\Set;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\security\EiCommandAccessRestrictor;
use rocket\spec\ei\EiCommandPath;

class EiMapping {
	private $mappingProfile;
	private $mappingErrorInfo;
	private $eiSelection;
	
	private $accessible = true;
	private $mappingConstraintSet = array();
	private $eiExecutionRestrictorSet = array();
	
	private $listeners = array();
	
	public function __construct(MappingProfile $mappingProfile, EiSelection $eiSelection) {
		$this->mappingProfile = $mappingProfile;
		$this->mappingErrorInfo = new MappingErrorInfo();
		$this->eiSelection = $eiSelection;
		$this->mappingConstraintSet = new HashSet(EiMappingConstraint::class);
		$this->eiExecutionRestrictorSet = new HashSet(EiCommandAccessRestrictor::class);
	}
	
	public function getIdRep() {
		$liveEntry = $this->eiSelection->getLiveEntry();
		if (!$liveEntry->isPersistent()) return null;
		
		return $this->getEiSpec()->idToIdRep($liveEntry->getId());
	}
	
	public function getId() {
		$liveEntry = $this->eiSelection->getLiveEntry();
		if (!$liveEntry->isPersistent()) return null;
		
		return $liveEntry->getId();
	}
	
	public function isNew() {
		return !$this->eiSelection->getLiveEntry()->isPersistent();
	}
	
	public function getEiSpec(): EiSpec {
		return $this->eiSelection->getLiveEntry()->getEiSpec();
	}
	
	public function setAccessible(bool $accessible) {
		$this->accessible = $accessible;
	}
	
	public function isAccessible(): bool {
		return $this->accessible;
	}
	
	public function getMappingProfile(bool $ignoreAccessRestriction = false): MappingProfile {
		if ($this->accessible || $ignoreAccessRestriction) {
			return $this->mappingProfile;
		}
		
		throw new InaccessibleEntryException();
	}
	/**
	 * @return \rocket\spec\ei\manage\EiSelection
	 */
	public function getEiSelection(): EiSelection {
		return $this->eiSelection;
	}
	
	public function getEiMappingConstraintSet(): Set {
		return $this->mappingConstraintSet;
	}
	
	public function getEiCommandAccessRestrictorSet(): Set {
		return $this->eiExecutionRestrictorSet;
	}
	
	public function isExecutableBy(EiCommandPath $eiCommandPath) {
		foreach ($this->eiExecutionRestrictorSet as $eiExecutionRestrictor) {
			if (!$eiExecutionRestrictor->isAccessibleBy($eiCommandPath)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function getValue($eiFieldPath, bool $ignoreAccessRestriction = false) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		return $this->getMappingProfile($ignoreAccessRestriction)->getMappable($eiFieldPath, $ignoreAccessRestriction)
				->getValue();
	}
	
	public function setValue($eiFieldPath, $value, bool $ignoreAccessRestriction = false) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		$this->getMappingProfile($ignoreAccessRestriction)->getMappable($eiFieldPath, $ignoreAccessRestriction)
				->setValue($value);
	}

	public function getOrgValue($eiFieldPath, bool $ignoreAccessRestriction = false) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		return $this->getMappingProfile($ignoreAccessRestriction)->getMappable($eiFieldPath, $ignoreAccessRestriction)
				->getOrgValue();
	}
	
// 	private function createSetOperationException(EiFieldPath $eiFieldPath, $code, \Exception $previous) {
// 		throw new MappingOperationFailedException('Could not set value for field \'' . $eiFieldPath 
// 						. '\' on  \'' . $this->determineEiSpec()->getId() . '\'. Reason: ' 
// 						. $previous->getMessage(), 
// 				0, $previous);
// 	}

// 	public function setValues(array $values) {
// 		foreach ($this->values as $id => $value) {
// 			$this->setValue($id, $value);
// 		}
// 	}
	
// 	public function getValues() {
// 		return $this->values;
// 	}
	
// 	public function getAllValues() {
// 		return $this->values + $this->getOrgValues();
// 	}
	
// 	public function getOrgValues() {
// 		foreach ($this->mappingProfile->getMappables() as $id => $mappable) {
// 			if (array_key_exists($id, $this->orgValues)) continue;
			
// 			$this->orgValues[$id] = $this->readOrgValue($mappable);
// 		}
		
// 		return $this->orgValues;
// 	}
	
// 	private function readOrgValue(Mappable $mappable) {
// 		if ($this->eiSelection->isDraft() && $mappable->isDraftable()) {
// 			return $mappable->draftRead($this->eiSelection->getDraft());
// 		}
		
// 		return $mappable->read($this->eiSelection->getEntityObj());
// 	}
	
// 	protected function setParentMapping(EiMapping $parentMapping) {
// 		$this->parentMapping = $parentMapping;
// 	}
	
// 	public function getParentMapping() {
// 		return $this->parentMapping;
// 	}
	
// 	public function registerRelatedMapping($id, EiMapping $relatedMapping) {
// 		if (!isset($this->relatedMappings[$id])) {
// 			$this->relatedMappings[$id] = array();
// 		}
		
// 		$this->relatedMappings[$id][] = $relatedMapping;
// 		$relatedMapping->setParentMapping($this);
// 	}
	
// 	public function unregisterRelatedMappings($id) {
// 		foreach ($this->getRelatedMappings($id) as $relatedMapping) {
// 			$relatedMapping->setParentMapping(null);
// 		}
// 		$this->relatedMappings[$id] = array();
// 	}
	
// 	public function hasRelatedMapping($id) {
// 		return isset($this->relatedMappings[$id]) && sizeof($this->relatedMappings[$id]);
// 	}
	
// 	public function getRelatedMapping($id) {
// 		if (!$this->hasRelatedMapping($id)) {
// 			throw new UnsupportedOperationException('No related mappings for ' . $id . ' available.');
// 		}
		
// 		if (1 < sizeof($this->relatedMappings[$id])) {
// 			throw new UnsupportedOperationException('More then one related mappings for ' . $id . ' available.');
// 		}
		
// 		return current($this->relatedMappings[$id]); 
// 	}
	
// 	public function getRelatedMappings($id) {
// 		if (!$this->hasRelatedMapping($id)) {
// 			return array();
// 		}
		
// 		return $this->relatedMappings[$id];
// 	}
	
	public function save(): bool {
		if (!$this->validate()) return false;
		$this->write();
		$this->flush();
		return true;
	}
	
	public function validate(): bool {
		if (!$this->accessible) {
			throw new InaccessibleEntryException();
		}
		
		$this->mappingErrorInfo = new MappingErrorInfo();
		
		foreach ($this->listeners as $listener) {
			$listener->onValidate($this);
		}
		
		$this->mappingProfile->validate($this->mappingErrorInfo);
		
		foreach ($this->mappingConstraintSet as $mappingConstraint) {
			$mappingConstraint->validate($this);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->validated($this);
		}
		
		return $this->mappingErrorInfo->isValid();
	}
	
	public function hasValidationResult(): bool {
		return $this->validateionResult !== null;
	}
	
	public function getMappingErrorInfo(): MappingErrorInfo {
		IllegalStateException::assertTrue($this->mappingErrorInfo !== null);
		
		return $this->mappingErrorInfo;
	}
	
	public function write() {
		foreach ($this->listeners as $listener) {
			$listener->onWrite($this);
		}
		
		$this->mappingProfile->write();
		
		foreach ($this->listeners as $listener) {
			$listener->written($this);
		}
	}
	
	private function flush() {
// 		foreach ($this->relatedMappings as $id => $relatedMappings) {
// 			foreach ($relatedMappings as $relatedMapping) {
// 				$relatedMapping->flush();
// 			}
// 		}
		
		foreach ($this->listeners as $listener) {
			$listener->flush($this);
		}
	}
	
	public function copy(EiMapping $targetMapping) {
		$targetMappingDefinition = $targetMapping->getMappingDefinition();
		$targetType = $targetMapping->getEiSelection()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingProfile->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
	}
	
	
	
// 	public function draftCopy(EiMapping $entityMapping) {
// 		foreach ($entityMapping->getWritables() as $id => $draftable) {
// 			if (isset($this->draftables[$id])) {
// 				$entityMapping->setValue($id, $this->draftables[$id]->draftCopy($this->getValue($id)));
// 			}
// 		}
// 	}
	
// 	public function translationCopy(EiMapping $entityMapping) {
		
// 	}
	
	
	public function registerListener(EiMappingListener $listener, $relatedFieldId = null) {
		$objectHash = spl_object_hash($listener);
		$this->listeners[$objectHash] = $listener;
		if (!isset($this->listenerBindings[$relatedFieldId])) {	
			$this->listenerBindings[$relatedFieldId][$objectHash] = $listener;
		}
	}
	
	public function executeOnWrite(\Closure $closure) {
		$this->registerListener(new OnWriteMappingListener($closure));
	}
	
	public function getFieldRelatedListeners($fieldId) {
		if (isset($this->listenerBindings[$fieldId])) {
			return $this->listenerBindings[$fieldId];
		}
		
		return array();
	}
	
	public function unregisterListener(EiMappingListener $listener) {
		$objectHash = spl_object_hash($listener);
		unset($this->listeners[$objectHash]);
		foreach ($this->listenerBindings as $fieldId => $listeners) {
			unset($this->listenerBindings[$fieldId][$objectHash]);
		}
	}
	
	public function unregisterFieldRelatedListeners($fieldId) {
		unset($this->listenerBindings[$fieldId]);
	}
	
// 	public function registerValidator(MappingValidator $validator) {
// 		$this->validators[spl_object_hash($validator)] = $validator;
// 	}
	
// 	public function registerConstraint(EiMappingConstraint $mappingConstraint) {
// 		$this->registerValidator($mappingConstraint);
// 		$this->mappingConstraintSet[spl_object_hash($mappingConstraint)] = $mappingConstraint;
// 	}
	
// 	public function unregisterValidator(MappingValidator $validator) {
// 		unset($this->validators[spl_object_hash($validator)]);
// 		unset($this->mappingConstraintSet[spl_object_hash($validator)]);
// 	}
	
	public function acceptsValue($eiFieldPath, $value) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		foreach ($this->mappingConstraintSet as $constraint) {
			if (!$constraint->acceptsValue($eiFieldPath, $value)) return false;
		}
		return true;
	}
	
	public function equals($obj) {
		return $obj instanceof EiMapping && $this->determineEiSpec()->equals($obj->determineEiSpec())
				&& $this->eiSelection->equals($obj->getEiSelection());
	}
	
	public function toEntryNavPoint() {
		return $this->eiSelection->toEntryNavPoint($this->contextEiSpec);
	}
}




class OnWriteMappingListener implements EiMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onValidate()
	 */
	public function onValidate(EiMapping $eiMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::validated()
	 */
	public function validated(EiMapping $eiMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onWrite()
	 */
	public function onWrite(EiMapping $eiMapping) {
		$this->closure->__invoke($eiMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::written()
	 */
	public function written(EiMapping $eiMapping) {}
	
	public function flush(EiMapping $eiMapping) {}

}

class WrittenMappingListener implements EiMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onValidate()
	 */
	public function onValidate(EiMapping $eiMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::validated()
	 */
	public function validated(EiMapping $eiMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onWrite()
	 */
	public function onWrite(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::written()
	 */
	public function written(EiMapping $eiMapping) {
		$this->closure->__invoke($eiMapping);
	}
	
	public function flush(EiMapping $eiMapping) {}
}

class OnValidateMappingListener implements EiMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onValidate()
	 */
	public function onValidate(EiMapping $eiMapping) { 
		$this->closure->__invoke($mappingValidationResult, $eiMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::validated()
	 */
	public function validated(EiMapping $eiMapping) { }
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onWrite()
	 */
	public function onWrite(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::written()
	 */
	public function written(EiMapping $eiMapping) {}
	
	public function flush(EiMapping $eiMapping) {}
}

class ValidatedMappingListener implements EiMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onValidate()
	 */
	public function onValidate(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::validated()
	 */
	public function validated(EiMapping $eiMapping) { 
		$this->closure->__invoke($mappingValidationResult, $eiMapping);
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onWrite()
	 */
	public function onWrite(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::written()
	 */
	public function written(EiMapping $eiMapping) {}
	
	public function flush(EiMapping $eiMapping) {}
}

class FlushMappingListener implements EiMappingListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onValidate()
	 */
	public function onValidate(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::validated()
	 */
	public function validated(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::onWrite()
	 */
	public function onWrite(EiMapping $eiMapping) {}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiMappingListener::written()
	 */
	public function written(EiMapping $eiMapping) {}
	
	public function flush(EiMapping $eiMapping) {
		$this->closure->__invoke($eiMapping);
	}
}

// class SimpleEiMappingConstraint implements EiMappingConstraint {
// 	private $closure;

// 	public function __construct(\Closure $closure) {
// 		$this->closure = $closure;
// 	}

// 	public function validate(EiMapping $eiMapping) {
// 		if (true === $this->closure->__invoke($eiMapping)) return;
		
// 		$eiSelectionMapp
// 	}
// }


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
