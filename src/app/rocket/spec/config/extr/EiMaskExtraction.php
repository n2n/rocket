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
namespace rocket\spec\config\extr;

use rocket\spec\ei\mask\model\DisplayScheme;
use n2n\util\ex\IllegalStateException;

class EiMaskExtraction {
	private $id;
	private $moduleNamespace;
	private $eiDefExtraction;
	private $guiOrder;
	private $subMaskIds = array();
	
	public function __construct($id, $moduleNamespace) {
		$this->id = $id;
		$this->moduleNamespace = $moduleNamespace;
	}

	/**
	 * @return string 
	 */
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}
	
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace($moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	/**
	 * @return EiDefExtraction
	 */
	public function getEiDefExtraction() {
		return $this->eiDefExtraction;
	}
	
	/**
	 * @param EiDefExtraction $eiDefExtraction
	 */
	public function setEiDefExtraction(EiDefExtraction $eiDefExtraction) {
		$this->eiDefExtraction = $eiDefExtraction;
	}
	
	/**
	 * @return DisplayScheme
	 */
	public function getDisplayScheme(): DisplayScheme {
		IllegalStateException::assertTrue($this->guiOrder !== null);
		return $this->guiOrder;
	}
	
	/**
	 * @param DisplayScheme $guiOrder
	 */
	public function setDisplayScheme(DisplayScheme $guiOrder) {
		$this->guiOrder = $guiOrder;
	}
	
	/**
	 * @return string[]
	 */
	public function getSubEiMaskIds() {
		return $this->subMaskIds;
	}

	/**
	 * @param string[] $subMaskIds
	 */
	public function setSubMaskIds(array $subMaskIds) {
		$this->subMaskIds = $subMaskIds;
	}
}
