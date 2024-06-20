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
namespace rocket\ui\si\api;

use n2n\util\type\attrs\DataMap;
use rocket\op\ei\manage\api\SiValCall;
use rocket\op\ei\manage\api\SiSortCall;
use rocket\op\ei\manage\api\SiControlCall;
use rocket\op\ei\manage\api\SiFieldCall;
use rocket\op\ei\manage\api\SiGetCall;
use n2n\web\http\controller\impl\ControllingUtils;

class SiApiCall {

	function __construct(private ?SiControlCall $controlCall, private ?SiFieldCall $fieldCall,
			private SiSortCall $sort, private SiGetCall $getCall, SiValCall $valCall) {

	}

	function getControlCall(): ?SiControlCall {
		return $this->controlCall;
	}

	static function parse(array $data): SiApiCall {
		$dataMap = new DataMap($data);

		return new SiApiCall(SiControlCall::parse($dataMap->reqArray('controlCall')),
				SiFieldCall::parse($dataMap->reqArray('fieldCall')),
				SiSortCall::parse($dataMap->reqArray('sortCall')),
				SiGetCall::parse($dataMap->reqArray('getCall')),
				SiValCall::parse($dataMap->reqArray('valCall')));
	}
}

const API_CONTROL_SECTION = 'execcontrol';
const API_FIELD_SECTION = 'callfield';
const API_GET_SECTION = 'get';
const API_VAL_SECTION = 'val';
const API_SORT_SECTION = 'sort';