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

use n2n\context\Lookupable;
use rocket\spec\ei\manage\util\model\EiuFrame;

class Eiu implements Lookupable {
	private $eiuFactory;
	private $eiuCtrl;
	private $eiuFrame;
	private $eiuGui;
	private $eiuEntry;
	private $eiuField;
	
	
	public function __construct(...$eiArgs) {
		$this->eiuFactory = new EiuFactory();
		$this->eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiuFrame = $this->eiuFactory->getEiuFrame(false);
		$this->eiuEntry = $this->eiuFactory->getEiuEntry(false);
		$this->eiuGui = $this->eiuFactory->getEiuGui(false);
		$this->eiuField = $this->eiuFactory->getEiuField(false);
	}
	
	public function ctrl(bool $required = true) {
		if ($this->eiuCtrl !== null || !$required) return $this->eiuCtrl;
		
		throw new EiuPerimeterException('EiuCtrl is unavailable.');
	}

	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null || !$required) return $this->eiuFrame;
		
		throw new EiuPerimeterException('EiuFrame is unavailable.');
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @param bool $assignToEiu
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null || !$required) return $this->eiuEntry;
	
		throw new EiuPerimeterException('EiuEntry is unavailable.');
	}
	
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null || !$required) return $this->eiuGui;
	
		throw new EiuPerimeterException('EiuGui is unavailable.');
	}
	
	public function field(bool $required = true) {
		if ($this->eiuField !== null || !$required) return $this->eiuField;
		
		throw new EiuPerimeterException('EiuField is unavailable.');
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->frame()->getN2nContext()->lookup($lookupId, $required);
	}
}
