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
use n2n\util\type\attrs\AttributesException;
use n2n\web\http\UploadDefinition;
use n2n\io\managed\impl\FileFactory;
use n2n\util\type\ArgUtils;

class SiInputFactory {
	
	private $fileMap = [];
	
	/**
	 * @param array $uploadDefinitions
	 */
	function __construct() {
		
	}
	
	/**
	 * @param UploadDefinition[] $uploadDefinitions
	 */
	function registerUploadDefinitions(array $uploadDefinitions) {
		ArgUtils::valArray($uploadDefinitions, UploadDefinition::class);
		
		$files = [];
		foreach ($uploadDefinitions as $key => $uploadDefinition) {
			if ($uploadDefinition->hasClientError()) {
				$files[] = FileFactory::createFromUploadDefinition($uploadDefinition);
			}
			
			test(array_keys($uploadDefinitions['fileInputs']));
			
		}
		
	}
	
	/**
	 * @param array $data
	 * @return SiInput
	 * @throws CorruptedSiInputDataException
	 */
	function create(array $data) {
		$input = new SiInput();
		
		foreach ($data as $entryData) {
			try {
				$input->addEntryInput($this->createEntry($entryData));
			} catch (AttributesException $e) {
				throw new CorruptedSiInputDataException(null, 0, $e);
			}
		}
		
		return $input;
	}
	
	/**
	 * @param array $data
	 * @return SiEntryInput
	 */
	function createEntry(array $data) {
		$dataSet = new DataSet($data);
		
		$siEntryInput = new SiEntryInput($dataSet->reqString('category'), $dataSet->reqString('buildupId'), 
				$dataSet->optString('id'));
		foreach ($dataSet->reqArray('fieldInputMap', 'array') as $fieldId => $fielData) {
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

class CorruptedSiInputDataException extends \Exception {
	
}
