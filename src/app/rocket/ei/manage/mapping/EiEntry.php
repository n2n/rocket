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
namespace rocket\ei\manage\mapping;

use n2n\l10n\Message;
use rocket\ei\manage\EiObject;
use rocket\ei\EiPropPath;
use rocket\ei\security\InaccessibleEntryException;
use n2n\util\ex\IllegalStateException;
use n2n\util\col\HashSet;
use rocket\ei\manage\mapping\impl\EiFieldWrapperImpl;
use rocket\ei\mask\EiMask;

class EiEntry {
	private $eiObject;
	private $eiMask;
	private $mappingErrorInfo;
	private $accessible = true;
	private $eiFieldWrappers = array();
	private $eiFieldForks = array();
	private $listeners = array();
	private $constraintSet;
	
	public function __construct(EiObject $eiObject, EiMask $eiMask) {
		$this->eiObject = $eiObject;
		$this->eiMask = $eiMask;
		$this->mappingErrorInfo = new MappingErrorInfo();
		$this->constraintSet = new HashSet(EiEntryConstraint::class);
	}
	
	/**
	 * @return string|null
	 */
	public function getPid() {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		if (!$eiEntityObj->isPersistent()) return null;
		
		return $this->getEiMask()->getEiType()->idToPid($eiEntityObj->getId());
	}
	
	/**
	 * @return mixed|null
	 */
	public function getId() {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		if (!$eiEntityObj->isPersistent()) return null;
		
		return $eiEntityObj->getId();
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->eiObject->isNew();
	}
	
	/**
	 * @return EiMask
	 */
	public function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiMask->getEiType();
	}
	
	/**
	 * @param bool $accessible
	 */
	public function setAccessible(bool $accessible) {
		$this->accessible = $accessible;
	}
	
	/**
	 * @return bool
	 */
	public function isAccessible(): bool {
		return $this->accessible;
	}
	
	/**
	 * @param bool $ignoreAccessRestriction
	 * @throws InaccessibleEntryException
	 */
	private function ensureAccessible($ignoreAccessRestriction) {
		if ($this->accessible || $ignoreAccessRestriction) {
			return;
		}
		
		throw new InaccessibleEntryException();
	}
	
	public function getConstraintSet() {
		return $this->constraintSet;
	}
	
// 	public function getEiCommandAccessRestrictors()  {
// 		return $this->eiCommandAccessRestrictors;
// 	}
	
// 	public function isExecutableBy(EiCommandPath $eiCommandPath) {
// 		foreach ($this->eiCommandAccessRestrictors as $eiExecutionRestrictor) {
// 			if (!$eiExecutionRestrictor->isAccessibleBy($eiCommandPath)) {
// 				return false;
// 			}
// 		}
	
// 		return true;
// 	}
	
	public function contains(EiPropPath $eiPropPath): bool {
		$eiPropPathStr = (string) $eiPropPath;
		return isset($this->eiFieldWrappers[$eiPropPathStr]) && isset($this->eiFieldForks[$eiPropPathStr]);
	}
	
	public function remove(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		unset($this->eiFieldWrappers[$eiPropPathStr]);
		unset($this->eiFieldForks[$eiPropPathStr]);
	}
	
	public function putEiField(EiPropPath $eiPropPath, EiField $eiField) {
		$eiPropPathStr = (string) $eiPropPath;
		return $this->eiFieldWrappers[$eiPropPathStr] = new EiFieldWrapperImpl($eiField);
	}
	
	public function removeEiField(EiPropPath $eiPropPath) {
		unset($this->eiFieldWrappers[(string) $eiPropPath]);
	}
	
	public function containsEiField(EiPropPath $eiPropPath): bool {
		return isset($this->eiFieldWrappers[(string) $eiPropPath]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws MappingOperationFailedException
	 * @return EiField
	 */
	public function getEiField(EiPropPath $eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		return $this->getEiFieldWrapper($eiPropPath, $ignoreAccessRestriction)->getEiField();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws MappingOperationFailedException
	 * @return EiFieldWrapper
	 */
	public function getEiFieldWrapper(EiPropPath $eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->eiFieldWrappers[$eiPropPathStr])) {
			throw new MappingOperationFailedException('No EiField defined for EiPropPath \'' . $eiPropPathStr
					. '\'.');
		}
	
		return $this->eiFieldWrappers[$eiPropPathStr];
	}
		
	public function getEiFieldWrappers() {
		return $this->eiFieldWrappers;
	}
	
	public function containsEiFieldFork(EiPropPath $eiPropPath): bool {
		return isset($this->eiFieldForks[(string) $eiPropPath]);
	}
	
	public function putEiFieldFork(EiPropPath $eiPropPath, EiFieldFork $eiFieldFork) {
		$this->eiFieldFork[(string) $eiPropPath] = $eiFieldFork;
	}
	
	public function removeEiFieldFork(EiPropPath $eiPropPath) {
		unset($this->eiFieldForks[(string) $eiPropPath]);
	}
	
	public function getEiFieldFork(EiPropPath $eiPropPath): EiFieldFork {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->eiFieldForks[$eiPropPathStr])) {
			throw new MappingOperationFailedException('No EiFieldFork defined for EiPropPath \''
					. $eiPropPathStr . '\'.');
		}
	
		return $this->eiFieldForks[$eiPropPathStr];
	}
	
	public function getEiFieldForks(): array {
		return $this->eiFieldForks;
	}
	
	// 	public function read($entity, EiPropPath $eiPropPath) {
	
	// 	}
	
	// 	public function readAll($entity) {
	// 		$values = array();
	// 		foreach ($this->eiFields as $id => $eiField) {
	// 			if ($eiField->isReadable()) {
	// 				$values[$id] = $eiField->read($entity);
	// 			}
	// 		}
	// 		return $values;
	// 	}
	
	
	public function registerListener(EiEntryListener $listener, $relatedFieldId = null) {
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
	
	public function unregisterListener(EiEntryListener $listener) {
		$objectHash = spl_object_hash($listener);
		unset($this->listeners[$objectHash]);
		foreach ($this->listenerBindings as $fieldId => $listeners) {
			unset($this->listenerBindings[$fieldId][$objectHash]);
		}
	}
	
	public function unregisterFieldRelatedListeners($fieldId) {
		unset($this->listenerBindings[$fieldId]);
	}
		
	public function write() {
		foreach ($this->listeners as $listener) {
			$listener->onWrite($this);
		}
	
		foreach ($this->eiFieldWrappers as $eiPropPathStr => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored()) continue;
			
			$eiFieldWrapper->getEiField()->write();
		}
	
		foreach ($this->listeners as $listener) {
			$listener->written($this);
		}
	}
	
	private function flush() {
		foreach ($this->listeners as $listener) {
			$listener->flush($this);
		}
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param mixed $value
	 * @return boolean
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value) {
		foreach ($this->constraintSet as $constraint) {
			if (!$constraint->acceptsValue($eiPropPath, $value)) return false;
		}
		return true;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	public function getEiObject(): EiObject {
		return $this->eiObject;
	}
	
	public function getValue($eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		return $this->getEiField($eiPropPath, $ignoreAccessRestriction)->getValue();
	}
	
	public function setValue($eiPropPath, $value, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		$this->getEiField($eiPropPath, $ignoreAccessRestriction)->setValue($value);
	}

	public function getOrgValue($eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		return $this->getMappingProfile($ignoreAccessRestriction)->getEiField($eiPropPath, $ignoreAccessRestriction)
				->getOrgValue();
	}
	
	public function save(): bool {
		if (!$this->validate()) return false;
		$this->write();
		$this->flush();
		return true;
	}
	
	public function validate(MappingErrorInfo $mappingErrorInfo = null): bool {
		if (!$this->accessible) {
			throw new InaccessibleEntryException();
		}
		
		if ($mappingErrorInfo === null) {
			$mappingErrorInfo = $this->mappingErrorInfo = new MappingErrorInfo();	
		}
		
		foreach ($this->listeners as $listener) {
			$listener->onValidate($this);
		}
		
		foreach ($this->eiFieldWrappers as $eiPropPathStr => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored()) continue;
			$eiFieldWrapper->getEiField()->validate($mappingErrorInfo->getFieldErrorInfo(EiPropPath::create($eiPropPathStr)));
		}
		
		foreach ($this->constraintSet as $constraint) {
			$constraint->validate($this);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->validated($this);
		}
		
		return $mappingErrorInfo->isValid();
	}
	
	public function hasValidationResult(): bool {
		return $this->validateionResult !== null;
	}
	
	public function getMappingErrorInfo(): MappingErrorInfo {
		IllegalStateException::assertTrue($this->mappingErrorInfo !== null);
		
		return $this->mappingErrorInfo;
	}
	
	public function copy(EiEntry $targetMapping) {
		$targetMappingDefinition = $targetMapping->getMappingDefinition();
		$targetType = $targetMapping->getEiObject()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingProfile->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
	}
	
	public function equals($obj) {
		return $obj instanceof EiEntry && $this->determineEiType()->equals($obj->determineEiType())
				&& $this->eiObject->equals($obj->getEiObject());
	}
	
	public function toEntryNavPoint() {
		return $this->eiObject->toEntryNavPoint($this->contextEiType);
	}
	
	public function __toString() {
		if ($this->eiObject->isDraft()) {
			return 'EiEntry (' . $this->eiObject->getDraft() . ')';
		}
		
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		
		return  'EiEntry (' . $this->eiObject->getEiEntityObj()->getEiType()->getEntityModel()->getClass()->getShortName()
				. '#' . ($eiEntityObj->hasId() ? $eiEntityObj->getPid() : 'new') . ')';
	}
}

class OnWriteMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}

}

class WrittenMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
	
	public function flush(EiEntry $eiEntry) {}
}

class OnValidateMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { 
		$this->closure->__invoke($eiEntry);
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}
}

class ValidatedMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { 
		$this->closure->__invoke($eiEntry);
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}
}

class FlushMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\mapping\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
}

// class SimpleEiEntryConstraint implements EiEntryConstraint {
// 	private $closure;

// 	public function __construct(\Closure $closure) {
// 		$this->closure = $closure;
// 	}

// 	public function validate(EiEntry $eiEntry) {
// 		if (true === $this->closure->__invoke($eiEntry)) return;
		
// 		$eiObjectMapp
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
