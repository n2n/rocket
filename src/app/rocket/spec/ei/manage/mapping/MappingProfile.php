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

class MappingProfile {
	private $mappables = array();
	private $mappableForks = array();
	
// 	public function getMappableIds() {
// 		return array_keys($this->mappables);
// 	}
	
	public function contains(EiFieldPath $eiFieldPath): bool {
		$eiFieldPathStr = (string) $eiFieldPath;
		return isset($this->mappables[$eiFieldPathStr]) && isset($this->mappableForks[$eiFieldPathStr]);
	}
	
	public function remove(EiFieldPath $eiFieldPath) {
		$eiFieldPathStr = (string) $eiFieldPath;
		unset($this->mappables[$eiFieldPathStr]);
		unset($this->mappableForks[$eiFieldPathStr]);
	}
	
	public function putMappable(EiFieldPath $eiFieldPath, Mappable $mappable) {
		$eiFieldPathStr = (string) $eiFieldPath;
		$this->mappables[$eiFieldPathStr] = $mappable;
	}

	public function removeMappable(EiFieldPath $eiFieldPath) {
		unset($this->mappables[(string) $eiFieldPath]);
	}
	
	public function containsMappable(EiFieldPath $eiFieldPath): bool {
		return isset($this->mappables[(string) $eiFieldPath]);
	}
	
	public function getMappable(EiFieldPath $eiFieldPath): Mappable {
		$eiFieldPathStr = (string) $eiFieldPath;
		if (!isset($this->mappables[$eiFieldPathStr])) {
			throw new MappingOperationFailedException('No Mappable defined for EiFieldPath \'' . $eiFieldPathStr . '\'.');
		}
		
		return $this->mappables[$eiFieldPathStr];
	}
	
	public function getMappables(): array {
		return $this->mappables;
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
		foreach ($this->mappables as $eiFieldPathStr => $mappable) {
			$mappable->validate($mappingErrorInfo->getFieldErrorInfo(EiFieldPath::create($eiFieldPathStr)));
		}
	}

	public function write() {
		foreach ($this->mappables as $eiFieldPathStr => $mappable) {
			$mappable->write();
		}
	}
}
