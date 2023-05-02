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
namespace rocket\si\content;

use rocket\si\control\SiControl;
use rocket\si\SiPayloadFactory;

class SiEntry implements \JsonSerializable {

	/**
	 * @var string|null
	 */
	private $idName;
	/**
	 * @var SiField[] $fields
	 */
	private $fields = [];
// 	/**
// 	 * @var SiField[] $contextFields
// 	 */
// 	private $contextFields = [];
	/**
	 * @var SiControl[] $controls
	 */	
	private $controls = [];
	
	/**
	 * @param string|null $idName
	 */
	function __construct(?string $id, ?string $idName) {
		$this->id = $id;
		$this->idName = $idName;
	}
	
	/**
	 * @return string
	 */
	function getMaskId() {
		return $this->maskId;
	}
	

	function setMaskId(string $maskId): static {
		$this->maskId = $maskId;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getIdName(): ?string {
		return $this->idName;
	}
	
	/**
	 * @param string|null $idName
	 */
	function setIdName(?string $idName) {
		$this->idName = $idName;
	}

	/**
	 * @return SiField[]
	 */
	function getFields() {
		return $this->fields;
	}

	/**
	 * @param SiField[] $fields key is propId 
	 */
	function setFields(array $fields) {
		$this->fields = $fields;
		return $this;
	}

	function putField(string $id, SiField $field): static {
		$this->fields[$id] = $field;
		return $this;
	}
	
// 	/**
// 	 * @param string $id
// 	 * @param SiField[] $contextSiFields
// 	 * @return \rocket\si\content\SiEntry
// 	 */
// 	function putContextFields(string $id, array $contextSiFields) {
// 		if (empty($contextSiFields)) {
// 			unset($this->contextFields[$id]);
// 			return;
// 		}
		
// 		ArgUtils::valArray($contextSiFields, SiField::class);
// 		$this->contextFields[$id] = $contextSiFields;
// 		return $this;
// 	}
	
	/**
	 * @return SiControl[] 
	 */
	function getControls() {
		return $this->controls;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return SiEntry
	 */
	function setControls(array $controls): static {
		$this->controls = $controls;
		return $this;
	}

	function putControl(string $id, SiControl $control): static {
		$this->controls[$id] = $control;
		return $this;
	}
	
	function jsonSerialize(): mixed {
		$fieldsArr = SiPayloadFactory::createDataFromFields($this->fields);
		
		return [
			'id' => $this->id,
			'idName' => $this->idName,
			'fieldMap' => $fieldsArr,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}

}
