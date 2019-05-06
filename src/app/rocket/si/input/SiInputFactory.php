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
namespace rocket\si\input;

use n2n\util\type\attrs\DataSet;

class SiInputFactory {
	
	/**
	 * @param array $uploadDefinitions
	 */
	function __construct(array $uploadDefinitions) {
		test(array_keys($uploadDefinitions));
	}
	
	/**
	 * @param array $data
	 * @return SiInput
	 */
	function create(array $data) {
		test($data);
		
// 		$dataSet = new DataSet($data);
		
// 		$input = new SiInput();
// 		foreach ($dataSet->reqArray('entryInputs', 'array') as $entryData) {
// 			$input->addEntryInput($this->createEntry($entryData));
// 		}
// 		return $input;
	}
	
	/**
	 * @param array $data
	 * @return SiEntryInput
	 */
	function createEntry(array $data) {
		$dataSet = new DataSet($data);
		
		$siEntryInput = new SiEntryInput($dataSet->optString('id'));
		foreach ($dataSet->reqArray('entryInputs', 'array') as $fieldId => $fielData) {
			$siEntryInput->setFieldInput($fieldId, $this->createField($fielData));
		}
		return $siEntryInput;
	}
	
	/**
	 * @param array $data
	 * @return \rocket\si\input\SiFieldInput
	 */
	function createField(array $data) {
		return new SiFieldInput($data);
	}
}
