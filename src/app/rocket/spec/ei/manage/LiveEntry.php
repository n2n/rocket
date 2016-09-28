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
namespace rocket\spec\ei\manage;

use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\EiSpec;
use n2n\reflection\ReflectionUtils;

class LiveEntry {
	private $persistent; 
	private $id;
	private $entityObj;
	private $eiSpec;
	
	private function __construct(bool $persistent, $id, $entityObj, EiSpec $eiSpec) {
		$this->id = $id;
		$this->entityObj = $entityObj;
		$this->eiSpec = $eiSpec;
		
		$this->setPersistent($persistent);
	}
	
	public function isPersistent(): bool {
		return $this->persistent;
	}
	
	public function setPersistent(bool $persistent) {
		if (!$persistent || $this->id !== null) {
			$this->persistent = $persistent;
			return;
		}
		
		throw new IllegalStateException('No id defined.');
	}
	
	public function hasId() {
		return $this->id !== null;
	}
	
	public function getId() {
		if ($this->id !== null) {
			return $this->id;
		}
		
		throw new IllegalStateException('Id not yet defined.');
	}
	
// 	public function getIdRep() {
// 		return $this->eiSpec->idToIdRep($this->getId());
// 	}
	
	public function refreshId() {
		$this->id = $this->eiSpec->extractId($this->entityObj);
	}
	
	public function getEntityObj() {
		return $this->entityObj;
	}
	
	public function getEiSpec(): EiSpec {
		return $this->eiSpec;
	}
	
	public function equals($obj) {
		return $obj instanceof LiveEntry && $this->getEntityObj() === $obj->getEntityObj();
	}
	
	public static function createFrom(EiSpec $contextEiSpec, $entityObj) {
		ArgUtils::valObject($entityObj, false, 'entityObj');
		
		return new LiveEntry(true, $contextEiSpec->extractId($entityObj), $entityObj, 
				$contextEiSpec->determineAdequateEiSpec(new \ReflectionClass($entityObj)));
	}
	
	public static function createNew(EiSpec $eiSpec) {
		$entityObj = ReflectionUtils::createObject($eiSpec->getEntityModel()->getClass());
		return new LiveEntry(false, null, $entityObj, $eiSpec);
	}
}
