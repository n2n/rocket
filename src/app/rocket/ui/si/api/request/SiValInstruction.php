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
namespace rocket\ui\si\api\request;

use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\input\SiEntryInput;

class SiValInstruction {
	/**
	 * @var SiEntryInput
	 */
	private $valueBoundaryInput;
	/**
	 * @var SiValGetInstruction[]
	 */
	private $getInstructions = [];

	function __construct(SiValueBoundaryInput $valueBoundaryInput) {
		$this->valueBoundaryInput = $valueBoundaryInput;
	}
	
	public function getValueBoundaryInput(): SiValueBoundaryInput {
		return $this->valueBoundaryInput;
	}
	
	/**
	 * @param \rocket\ui\si\input\SiEntryInput $valueBoundaryInput
	 */
	public function setValueBoundaryInput($valueBoundaryInput) {
		$this->valueBoundaryInput = $valueBoundaryInput;
	}

	/**
	 * @return \rocket\ui\si\api\SiValGetInstruction[]
	 */
	public function getGetInstructions() {
		return $this->getInstructions;
	}

	/**
	 * @param \rocket\ui\si\api\SiValGetInstruction[]  $getInstructions
	 */
	public function setGetInstructions(array $getInstructions) {
		ArgUtils::valArray($getInstructions, SiValGetInstruction::class);
		$this->getInstructions = $getInstructions;
	}
	
	/**
	 * @param string $key
	 * @param SiValGetInstruction $getInstruction
	 */
	function putGetInstruction(string $key, SiValGetInstruction $getInstruction) {
		$this->getInstructions[$key] = $getInstruction;
	}
	
	/**
	 * @param array $data
	 * @return \rocket\ui\si\api\SiValRequest
	 *@throws \InvalidArgumentException
	 */
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		$valInstruction = new SiValInstruction(SiEntryInput::parse($ds->reqArray('entryInput')));
		try {
			foreach ($ds->reqArray('getInstructions') as $key => $instructionData) {
				$valInstruction->putGetInstruction($key, SiValGetInstruction::createFromData($instructionData));
			}
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
		return $valInstruction;
	}
}
