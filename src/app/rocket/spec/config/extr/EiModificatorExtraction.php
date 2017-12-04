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

class EiModificatorExtraction {
	private $id;
	private $moduleNamespace;
	private $eiTypeId;
	private $commonEiMaskId;
	private $className;
	private $props = array();
	
	public function __construct(string $id, string $moduleNamespace, string $eiTypeId, string $commonEiMaskId = null) {
		$this->id = $id;
		$this->moduleNamespace = $moduleNamespace;
		$this->eiTypeId = $eiTypeId;
		$this->commonEiMaskId = $commonEiMaskId;
	}
	
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

	public function getEiTypeId() {
		return $this->eiTypeId;
	}

	public function setEiTypeId($eiTypeId) {
		$this->eiTypeId = $eiTypeId;
	}

	public function getCommonEiMaskId() {
		return $this->commonEiMaskId;
	}

	public function setCommonEiMaskId($commonEiMaskId) {
		$this->commonEiMaskId = $commonEiMaskId;
	}

	public function getClassName() {
		return $this->className;
	}

	public function setClassName($className) {
		$this->className = $className;
	}
	
	public function getProps() {
		return $this->props;
	}

	public function setProps(array $props) {
		$this->props = $props;
	}
}
