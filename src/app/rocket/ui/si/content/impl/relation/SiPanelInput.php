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
namespace rocket\ui\si\content\impl\relation;

use rocket\ui\si\api\request\SiEntryInput;
use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataSet;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\api\request\SiValueBoundaryInput;

class SiPanelInput {
	/**
	 * @var string
	 */
	private string $name;
	/**
	 * @var SiValueBoundaryInput[]
	 */
	private array $valueBoundaryInputs = [];

	/**
	 * @param string $name
	 */
	function __construct(string $name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	function getName(): string {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * @return SiValueBoundaryInput[]
	 */
	function getValueBoundaryInputs(): array {
		return $this->valueBoundaryInputs;
	}
	
	function setValueBoundaryInputs(array $valueBoundaryInputs): void {
		ArgUtils::valArray($valueBoundaryInputs, SiValueBoundaryInput::class);
		$this->valueBoundaryInputs = $valueBoundaryInputs;
	}
	
	/**
	 * @param array $data
	 * @return SiPanelInput
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiPanelInput {
		$dataSet = new DataSet($data);
		
		try {
			$panelInput = new SiPanelInput($dataSet->reqString('name'));
			$valueBoundaryInputs = [];
			foreach ($dataSet->reqArray('valueBoundaryInputs', 'array') as $entryInputData) {
				$valueBoundaryInputs[] = SiValueBoundaryInput::parse($entryInputData);
			}
			$panelInput->setValueBoundaryInputs($valueBoundaryInputs);
			return $panelInput;
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new CorruptedSiDataException(null, 0, $e);
		}
	}
}
