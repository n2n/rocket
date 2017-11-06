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
namespace rocket\spec\ei\manage\util\model;

class EiuField {
	private $eiPropPath;
	private $eiuEntry;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiPropPath = $eiuFactory->getEiPropPath(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
	}
	
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('EiuEntry unavailable.');
	}
	
	public function getValue() {
		return $this->getEiuEntry()->getValue($this->eiPropPath);
	}
	
	public function setValue($value) {
		return $this->getEiuEntry()->setValue($this->eiPropPath, $value);
	}
	
	public function setScalarValue($scalarValue) {
		return $this->getEiuEntry()->setScalarValue($this->eiPropPath, $scalarValue);
	}
}
