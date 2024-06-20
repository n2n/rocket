<?php

namespace rocket\ui\si\api;

use n2n\util\type\attrs\DataMap;
use rocket\op\ei\manage\api\SiValCall;
use rocket\op\ei\manage\api\SiSortCall;
use rocket\op\ei\manage\api\SiControlCall;
use rocket\op\ei\manage\api\SiFieldCall;
use rocket\op\ei\manage\api\SiGetCall;

class SiApiCall {

	function __construct(private ?SiControlCall $controlCall, private ?SiFieldCall $fieldCall,
			private SiSortCall $sort, private SiGetCall $getCall, SiValCall $valCall) {

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