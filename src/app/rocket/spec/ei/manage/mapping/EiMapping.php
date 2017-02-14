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
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\util\ex\IllegalStateException;
use n2n\util\col\HashSet;
use rocket\spec\ei\manage\mapping\impl\MappableWrapperImpl;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\security\EiCommandAccessRestrictor;

class EiMapping {
	private $mappingErrorInfo;
	private $eiSelection;
	private $accessible = true;
	private $mappableWrappers = array();
	private $mappableForks = array();
	private $listeners = array();
	private $constraints;
	private $eiCommandAccessRestrictors;
	
	public function __construct(EiSelection $eiSelection) {
		$this->mappingErrorInfo = new MappingErrorInfo();
		$this->eiSelection = $eiSelection;
		$this->constraints = new HashSet(EiMappingConstraint::class);
		$this->eiCommandAccessRestrictors = new HashSet(EiCommandAccessRestrictor::class);
	}
	
	/**
	 * @return string|null
	 */
	public function getIdRep() {
		$liveEntry = $this->eiSelection->getLiveEntry();
		if (!$liveEntry->isPersistent()) return null;
		
		return $this->getEiSpec()->idToIdRep($liveEntry->getId());
	}
	
	/**
	 * @return mixed|null
	 */
	public function getId() {
		$liveEntry = $this->eiSelection->getLiveEntry();
		if (!$liveEntry->isPersistent()) return null;
		
		return $liveEntry->getId();
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return !$this->eiSelection->getLiveEntry()->isPersistent();
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpec() {
		return $this->eiSelection->getLiveEntry()->getEiSpec();
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
	
	public function contains(EiFieldPath $eiFieldPath): bool {
		$eiFieldPathStr = (string) $eiFieldPath;
		return isset($this->mappableWrappers[$eiFieldPathStr]) && isset($this->mappableForks[$eiFieldPathStr]);
	}
	
	public function remove(EiFieldPath $eiFieldPath) {
		$eiFieldPathStr = (string) $eiFieldPath;
		unset($this->mappableWrappers[$eiFieldPathStr]);
		unset($this->mappableForks[$eiFieldPathStr]);
	}
	
	public function putMappable(EiFieldPath $eiFieldPath, Mappable $mappable) {
		$eiFieldPathStr = (string) $eiFieldPath;
		return $this->mappableWrappers[$eiFieldPathStr] = new MappableWrapperImpl($mappable);
	}
	
	public function removeMappable(EiFieldPath $eiFieldPath) {
		unset($this->mappableWrappers[(string) $eiFieldPath]);
	}
	
	public function containsMappable(EiFieldPath $eiFieldPath): bool {
		return isset($this->mappableWrappers[(string) $eiFieldPath]);
	}
	
	/**
	 * @param EiFieldPath $eiFieldPath
	 * @throws MappingOperationFailedException
	 * @return Mappable
	 */
	public function getMappable(EiFieldPath $eiFieldPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		return $this->getMappableWrapper($eiFieldPath, $ignoreAccessRestriction)->getMappable();
	}
	
	/**
	 * @param EiFieldPath $eiFieldPath
	 * @throws MappingOperationFailedException
	 * @return MappableWrapper
	 */
	public function getMappableWrapper(EiFieldPath $eiFieldPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiFieldPathStr = (string) $eiFieldPath;
		if (!isset($this->mappableWrappers[$eiFieldPathStr])) {
			throw new MappingOperationFailedException('No Mappable defined for EiFieldPath \'' . $eiFieldPathStr
					. '\'.');
		}
	
		return $this->mappableWrappers[$eiFieldPathStr];
	}
	
	public function getMappableWrappers() {
		return $this->mappableWrappers;
	}
	
	public function containsMappableFork(EiFieldPath $eiFieldPath): bool {
		return isset($this->mappableForks[(string) $eiFieldPath]);
	}
	
	public function putMappableFork(EiFieldPath $eiFieldPath, MappableFork $mappableFork) {
		$this->mappableFork[(string) $eiFieldPath] = $mappableFork;
	}
	
	public function removeMappableFork(EiFieldPath $eiFieldPath) {
		unset($this->mappableForks[(string) $eiFieldPath]);
	}
	
	public function getMappableFork(EiFieldPath $eiFieldPath): MappableFork {
		$eiFieldPathStr = (string) $eiFieldPath;
		if (!isset($this->mappableForks[$eiFieldPathStr])) {
			throw new MappingOperationFailedException('No MappableFork defined for EiFieldPath \''
					. $eiFieldPathStr . '\'.');
		}
	
		return $this->mappableForks[$eiFieldPathStr];
	}
	
	public function getMappableForks(): array {
		return $this->mappableForks;
	}
	
	// 	public function read($entity, EiFieldPath $eiFieldPath) {
	
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
	
		foreach ($this->mappableWrappers as $eiFieldPathStr => $mappableWrapper) {
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
	 * @param EiFieldPath $eiFieldPath
	 * @param unknown $value
	 * @return boolean
	 */
	public function acceptsValue(EiFieldPath $eiFieldPath, $value) {
		foreach ($this->constraints as $constraint) {
			if (!$constraint->acceptsValue($eiFieldPath, $value)) return false;
		}
		return true;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiSelection
	 */
	public function getEiSelection(): EiSelection {
		return $this->eiSelection;
	}
	
	public function getValue($eiFieldPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		return $this->getMappable($eiFieldPath, $ignoreAccessRestriction)->getValue();
	}
	
	public function setValue($eiFieldPath, $value, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		$this->getMappable($eiFieldPath, $ignoreAccessRestriction)->setValue($value);
	}

	public function getOrgValue($eiFieldPath, bool $ignoreAccessRestriction = false) {
		$this->ensureAccessible($ignoreAccessRestriction);
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		
		return $this->getMappingProfile($ignoreAccessRestriction)->getMappable($eiFieldPath, $ignoreAccessRestriction)
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
		
		foreach ($this->mappableWrappers as $eiFieldPathStr => $mappableWrapper) {
			if ($mappableWrapper->isIgnored()) continue;
			$mappableWrapper->getMappable()->validate($mappingErrorInfo->getFieldErrorInfo(EiFieldPath::create($eiFieldPathStr)));
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
		$targetType = $targetMapping->getEiSelection()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingProfile->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
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
