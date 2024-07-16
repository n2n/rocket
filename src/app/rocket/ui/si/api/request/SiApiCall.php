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

use n2n\util\type\attrs\DataMap;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\AttributesException;

class SiApiCall implements \JsonSerializable {

	function __construct(private ?SiInput $input = null, private ?SiControlCall $controlCall = null,
			private ?SiFieldCall $fieldCall = null, private ?SiSortCall $sortCall = null,
			private ?SiGetRequest $getRequest = null, private ?SiValRequest $valRequest = null) {

	}

	function getInput(): ?SiInput {
		return $this->input;
	}

	function getControlCall(): ?SiControlCall {
		return $this->controlCall;
	}

	function getFieldCall(): ?SiFieldCall {
		return $this->fieldCall;
	}

	function getGetRequest(): ?SiGetRequest {
		return $this->getRequest;
	}

	function getValRequest(): ?SiValRequest {
		return $this->valRequest;
	}

	function getSortCall(): ?SiSortCall {
		return $this->sortCall;
	}

	public function jsonSerialize(): mixed {
		return [
			'input' => $this->input,
			'controlCall' => $this->controlCall,
			'fieldCall' => $this->fieldCall,
			'sortCall' => $this->sortCall,
			'getRequest' => $this->getRequest,
			'valRequest' => $this->valRequest
		];
	}

	/**
	 * @param array $data
	 * @return SiApiCall
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiApiCall {
		$dataMap = new DataMap($data);

		try {
			return new SiApiCall(SiInput::parse($dataMap->optArray('input', defaultValue: null, nullAllowed: true)),
					SiControlCall::parse($dataMap->optArray('controlCall', defaultValue: null, nullAllowed: true)),
					SiFieldCall::parse($dataMap->optArray('fieldCall', defaultValue: null, nullAllowed: true)),
					SiSortCall::parse($dataMap->optArray('sortCall', defaultValue: null, nullAllowed: true)),
					SiGetRequest::parse($dataMap->optArray('getRequest', defaultValue: null, nullAllowed: true)),
					SiValRequest::parse($dataMap->optArray('valRequest', defaultValue: null, nullAllowed: true)));
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('Could not parse SiApiCall.', previous: $e);
		}
	}
}

const API_CONTROL_SECTION = 'execcontrol';
const API_FIELD_SECTION = 'callfield';
const API_GET_SECTION = 'get';
const API_VAL_SECTION = 'val';
const API_SORT_SECTION = 'sort';