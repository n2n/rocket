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
namespace rocket\spec\ei\component\impl;

use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\component\EiComponent;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\EiEngine;

abstract class EiComponentAdapter implements EiComponent {
	protected $eiEngine;
	protected $id;
	
	/**
	 * @return 
	 */
	public function getEiEngine(): EiEngine {
		if ($this->eiEngine !== null) {
			return $this->eiEngine;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to an EiThing.');
	}
	
	public function setEiEngine(EiEngine $eiEngine) {
		$this->eiEngine = $eiEngine;
	}
	
	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getIdBase() {
		return ReflectionUtils::buildTypeAcronym(get_class($this));
	}
	
	public function equals($obj) {
		return $obj instanceof EiComponent && $this->id == $obj->getId();
	}
	
	public function __toString(): string {
		$detailStrs = array();
		$detailStrs[] = 'id: ' . ($this->id ?? 'null');
		if ($this->eiEngine === null) {
			$detailStrs[] = 'unassigned';
		} else {
			$detailStrs[] = $this->eiEngine->getEiType() ?? 'no EiType';
			if (null !== ($eiMask = $this->eiEngine->getEiMask())) {
				$detailStrs[] = (string) $eiMask;
			}
		}
		return (new \ReflectionClass($this))->getShortName() . ' [' . implode(', ', $detailStrs) . ']';
	}
}
