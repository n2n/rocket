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
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\util\ex\IllegalStateException;
use n2n\util\col\HashSet;
use rocket\spec\ei\manage\mapping\impl\MappableWrapperImpl;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\security\EiCommandAccessRestrictor;

class EiMapping {
	private $mappingErrorInfo;
	private $eiObject;
	private $accessible = true;
	private $mappableWrappers = array();
	private $mappableForks = array();
	private $listeners = array();
	private $constraints;
	private $eiCommandAccessRestrictors;
	
	public function __construct(EiObject $eiObject) {
		$this->mappingErrorInfo = new MappingErrorInfo();
		$this->eiObject = $eiObject;
		$this->constraints = new HashSet(EiMappingConstraint::class);
		$this->eiCommandAccessRestrictors = new HashSet(EiCommandAccessRestrictor::class);
	}
	
	/**
	 * @return string|null
	 */
	public function getIdRep() {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		if (!$eiEntityObj->isPersistent()) return null;
		
		return $this->getEiSpec()->idToIdRep($eiEntityObj->getId());
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
		return !$this->eiObject->getEiEntityObj()->isPersistent();
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpec() {
		return $this->eiObject->getEiEntityObj()->getEiSpec();
	}
	
	public function setAccessible(bool $accessible) {
		$this->accessible = $accessible;
	}
	
	public function isAccessible(): bool {
		return $this->accessible;
	}
	
	private function ensureAccessible($ignoreAccessRestriction) {
		if ($this->accessible || $ignoreAccessRestriction) {
			return;
		}
		
		throw new InaccessibleEntryException();
	}
	
	public function getEiMappingConstraints() {
		return $this->constraints;
	}
	
	public function getEiCommandAccessRestrictors()  {
		return $this->eiCommandAccessRestrictors;
	}
	
	public function isExecutableBy(EiCommandPath $eiCommandPath) {
		foreach ($this->eiCommandAccessRestrictors as $eiExecutionRestrictor) {
			if (!$eiExecutionRestrictor->isAccessibleBy($eiCommandPath)) {
				return false;
			}
		}
	
		return true;
	}
	
	public function contains(EiPropPath $eiPropPath): bool {
		$eiPropPathStr = (string) $eiPropPath;
		return isset($this->mappableWrappers[$eiPropPathStr]) && isset($this->mappableForks[$eiPropPathStr]);
	}
	
	public function remove(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		unset($this->mappableWrappers[$eiPropPathStr]);
		unset($this->mappableForks[$eiPropPathStr]);
	}
	
	public function putMappable(EiPropPath $eiPropPath, Mappable $mappable) {
		$eiPropPathStr = (string) $eiPropPath;
		return $this->mappableWrappers[$eiPropPathStr] = new MappableWrapperImpl($mappable);
	}
	
	public function removeMappable(EiPropPath $eiPropPath) {
		unset($this->mappableWrappers[(string) $eiPropPath]);
	}
	
	public function containsMappable(EiPropPath $eiPropPath): bool {
		return isset($this->mappableWrappers[(string) $eiPropPath]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws MappingOperationFailedException
	 * @return Mappable
	 */
	public function getMappable(EiPropPath $eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		return $this->getMappableWrapper($eiPropPath, $ignoreAccessRestriction)->getMappable();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws MappingOperationFailedException
	 * @return MappableWrapper
	 */
	public function getMappableWrapper(EiPropPath $eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->mappableWrappers[$eiPropPathStr])) {
			throw new MappingOperationFailedException('No Mappable defined for EiPropPath \'' . $eiPropPathStr
					. '\'.');
		}
	
		return $this->mappableWrappers[$eiPropPathStr];
	}
		
	public function getMappableWrappers() {
		return $this->mappableWrappers;
	}
	
	public function containsMappableFork(EiPropPath $eiPropPath): bool {
		return isset($this->mappableForks[(string) $eiPropPath]);
	}
	
	public function putMappableFork(EiPropPath $eiPropPath, MappableFork $mappableFork) {
		$this->mappableFork[(string) $eiPropPath] = $mappableFork;
	}
	
	public function removeMappableFork(EiPropPath $eiPropPath) {
		unset($this->mappableForks[(string) $eiPropPath]);
	}
	
	public function getMappableFork(EiPropPath $eiPropPath): MappableFork {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->mappableForks[$eiPropPathStr])) {
			throw new MappingOperationFailedException('No MappableFork defined for EiPropPath \''
					. $eiPropPathStr . '\'.');
		}
	
		return $this->mappableForks[$eiPropPathStr];
	}
	
	public function getMappableForks(): array {
		return $this->mappableForks;
	}
	
	// 	public function read($entity, EiPropPath $eiPropPath) {
	
	// 	}
	
	// 	public function readAll($entity) {
	// 		$values = array();
	// 		foreach ($this->mappables as $id => $mappable) {
	// 			if ($mappable->isReadable()) {
	// 				$values[$id] = $mappable->read($entity);
	// 			}
	// 		}
	// 		return $values;
	// 	}
	
	
	public function registerListener(EiMappingListener $listener, $relatedFieldId = null) {
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
		
	public function write() {
		foreach ($this->listeners as $listener) {
			$listener->onWrite($this);
		}
	
		foreach ($this->mappableWrappers as $eiPropPathStr => $mappableWrapper) {
			if ($mappableWrapper->isIgnored()) continue;
			
			$mappableWrapper->getMappable()->write();
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
	 * @param unknown $value
	 * @return boolean
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value) {
		foreach ($this->constraints as $constraint) {
			if (!$constraint->acceptsValue($eiPropPath, $value)) return false;
		}
		return true;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiObject
	 */
	public function getEiObject(): EiObject {
		return $this->eiObject;
	}
	
	public function getValue($eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		return $this->getMappable($eiPropPath, $ignoreAccessRestriction)->getValue();
	}
	
	public function setValue($eiPropPath, $value, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		$this->getMappable($eiPropPath, $ignoreAccessRestriction)->setValue($value);
	}

	public function getOrgValue($eiPropPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		return $this->getMappingProfile($ignoreAccessRestriction)->getMappable($eiPropPath, $ignoreAccessRestriction)
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
		
		foreach ($this->mappableWrappers as $eiPropPathStr => $mappableWrapper) {
			if ($mappableWrapper->isIgnored()) continue;
			$mappableWrapper->getMappable()->validate($mappingErrorInfo->getFieldErrorInfo(EiPropPath::create($eiPropPathStr)));
		}
		
		foreach ($this->constraints as $constraint) {
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
	
	public function copy(EiMapping $targetMapping) {
		$targetMappingDefinition = $targetMapping->getMappingDefinition();
		$targetType = $targetMapping->getEiObject()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingProfile->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
	}
	
	public function equals($obj) {
		return $obj instanceof EiMapping && $this->determineEiSpec()->equals($obj->determineEiSpec())
				&& $this->eiObject->equals($obj->getEiObject());
	}
	
	public function toEntryNavPoint() {
		return $this->eiObject->toEntryNavPoint($this->contextEiSpec);
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
		$this->closure->__invoke($eiMapping);
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
		$this->closure->__invoke($eiMapping);
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
