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
namespace rocket\spec\extr;

use rocket\spec\TypePath;

class EiTypeExtensionExtraction {
	private $id;
	private $moduleNamespace;
	private $extendedTypePath;
	private $eiMaskExtraction;
	private $subMaskIds = array();
	
	public function __construct(string $id, string $moduleNamespace, TypePath $extendedTypePath) {
		$this->id = $id;
		$this->moduleNamespace = $moduleNamespace;
		$this->extendedTypePath = $extendedTypePath;
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
	public function setId(string $id) {
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @param string $moduleNamespace
	 */
	public function setModuleNamespace(string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	/**
	 * @return \rocket\spec\TypePath
	 */
	public function getExtendedTypePath() {
		return $this->extendedTypePath;
	}
	
	/**
	 * @param TypePath $extendedTypePath
	 */
	public function setExtendedTypePath(TypePath $extendedTypePath) {
		$this->extendedTypePath = $extendedTypePath;
	}
	
	/**
	 * @return EiMaskExtraction
	 */
	public function getEiMaskExtraction() {
		return $this->eiMaskExtraction ?? $this->eiMaskExtraction = new EiMaskExtraction();
	}
	
	/**
	 * @param EiMaskExtraction $eiMaskExtraction
	 */
	public function setEiMaskExtraction(EiMaskExtraction $eiMaskExtraction) {
		$this->eiMaskExtraction = $eiMaskExtraction;
	}
	
	/**
	 * @return string|null
	 */
	public function getSuperEiTypeExtensionId() {
		return $this->superEiTypeExtensionId;
	}

	/**
	 * @param string|null $superEiTypeExtensionId
	 */
	public function setSuperEiTypeExtensionId(?string $superEiTypeExtensionId) {
		$this->$superEiTypeExtensionId = $superEiTypeExtensionId;
	}
}
