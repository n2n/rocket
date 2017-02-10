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

use rocket\spec\ei\EiFieldPath;
use n2n\util\col\HashSet;
use n2n\util\col\Set;
use rocket\spec\ei\security\EiCommandAccessRestrictor;
use rocket\spec\ei\EiCommandPath;

class MappingProfile {
	private $mappableWrappers = array();
	private $mappableForks = array();
	private $listeners = array();
	private $constraints;
	private $eiCommandAccessRestrictors;
	
	public function __construct() {
		$this->constraints = new HashSet(EiMappingConstraint::class);
		$this->eiCommandAccessRestrictors = new HashSet(EiCommandAccessRestrictor::class); 
	}
	
	public function getEiMappingConstraints(): Set {
		return $this->constraints;
	}
	
	public function getEiCommandAccessRestrictors(): Set {
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
		return $this->mappableWrappers[$eiFieldPathStr] = new MappableWrapper($mappable);
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
	public function getMappable(EiFieldPath $eiFieldPath) {
		return $this->getMappableWrapper($eiFieldPath)->getMappable();
	}
	
	/**
	 * @param EiFieldPath $eiFieldPath
	 * @throws MappingOperationFailedException
	 * @return MappableWrapper
	 */
	public function getMappableWrapper(EiFieldPath $eiFieldPath) {
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

	public function validate(MappingErrorInfo $mappingErrorInfo) {
		foreach ($this->listeners as $listener) {
			$listener->onValidate($this);
		}
		
		foreach ($this->mappableWrappers as $eiFieldPathStr => $mappableWrapper) {
			$mappableWrapper->getMappable()->validate($mappingErrorInfo->getFieldErrorInfo(EiFieldPath::create($eiFieldPathStr)));
		}
		
		foreach ($this->constraints as $constraint) {
			$constraint->validate($this);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->validated($this);
		}
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
	
	public function flush() {
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
		foreach ($this->mappingConstraints as $constraint) {
			if (!$constraint->acceptsValue($eiFieldPath, $value)) return false;
		}
		return true;
	}
}