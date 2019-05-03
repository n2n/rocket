<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser  Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser  Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\si\structure;

use n2n\util\type\ArgUtils;
use rocket\si\content\SiEntry;
use rocket\si\control\SiControl;

class SiBulkyDeclaration implements \JsonSerializable {
	private $fieldStructureDeclarations;
	private $controls;
	
	/**
	 * @param SiFieldStructureDeclaration[] $fieldStructureDeclarations
	 * @param SiEntry[] $siEntries
	 * @param SiControl[] $siControls;
	 */
	function __construct(array $fieldStructureDeclarations = [],
			array $siControls = []) {
		$this->setFieldStructureDeclarations($fieldStructureDeclarations);
		$this->setControls($siControls);
	}
	
	/**
	 * @param SiFieldStructureDeclaration[] $fieldStructureDeclarations
	 * @return \rocket\si\structure\SiBulkyDeclaration
	 */
	function setFieldStructureDeclarations(array $fieldStructureDeclarations) {
		ArgUtils::valArray($fieldStructureDeclarations, SiFieldStructureDeclaration::class);
		$this->fieldStructureDeclarations = $fieldStructureDeclarations;
		return $this;
	}
	
	function putFieldStructureDeclaration(string $buildupId, SiFieldStructureDeclaration $fieldStructureDeclaration) {
		$this->fieldStructureDeclarations[$buildupId] = $fieldStructureDeclaration;
	}
	
	/**
	 * @return SiFieldStructureDeclaration[]
	 */
	function getFieldStructureDeclarations() {
		return $this->fieldStructureDeclarations;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return \rocket\si\structure\SiBulkyDeclaration
	 */
	function setControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		$this->controls = $controls;
		return $this;
	}
	
	/**
	 * @return SiControl[]
	 */
	function getControls() {
		return $this->controls;
	}
	
	function jsonSerialize() {
		$controlsArr = array();
		foreach ($this->controls as $id => $control) {
			$controlsArr[$id] = [
				'type' => $control->getType(),
				'data' => $control->getData()
			];
		}
		
		return [
			'fieldStructureDeclarations' => $this->fieldStructureDeclarations,
			'entries' => $this->entries,
			'controls' => $controlsArr
		];
	}
}