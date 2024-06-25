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
use rocket\ui\si\input\SiInput;

class SiApiCall {

	function __construct(private ?SiInput $input, private ?SiControlCall $controlCall, private ?SiFieldCall $fieldCall,
			private ?SiSortCall $sortCall, private SiGetRequest $getRequest, private SiValRequest $valRequest) {

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

	/**
	 * @param array $data
	 * @return SiApiCall
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiApiCall {
		$dataMap = new DataMap($data);

		try {
			return new SiApiCall(SiInput::parse($dataMap->reqArray('input')),
					SiControlCall::parse($dataMap->reqArray('controlCall')),
					SiFieldCall::parse($dataMap->reqArray('fieldCall')),
					SiSortCall::parse($dataMap->reqArray('sortCall')),
					SiGetRequest::parse($dataMap->reqArray('getRequest')),
					SiValRequest::parse($dataMap->reqArray('valRequest')));
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