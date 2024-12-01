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

use n2n\web\http\controller\impl\ControllingUtils;
use n2n\web\http\Method;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\web\http\StatusException;
use n2n\web\http\BadRequestException;

class SiZoneCall implements \JsonSerializable {

	function __construct(private ?SiInput $input, private string $zoneControlName) {

	}

	function getInput(): ?SiInput {
		return $this->input;
	}

	function getZoneControlName(): string {
		return $this->zoneControlName;
	}


	/**
	 * @throws StatusException
	 */
	static function fromCu(ControllingUtils $cu): ?SiZoneCall {
		if (!($cu->getRequest()->getMethod() === Method::POST)) {
			return null;
		}

		$param = $cu->getParamPost('call');
		if ($param === null) {
			return null;
		}

		$httpData = $param->parseJsonToHttpData();
		try {
			$siControlCall = SiControlCall::parse($httpData->reqArray('controlCall'));
		} catch (CorruptedSiDataException $e) {
			throw new BadRequestException(previous: $e);
		}

		if ($siControlCall->getMaskId() !== null || $siControlCall->getEntryId() !== null) {
			throw new BadRequestException('ZoneCall can not handle controls of specific masks oder entries.'
					. 'Problem: maskId or entryId was provided.');
		}

		$zoneControlName = $siControlCall->getControlName();

		$siInput = null;
		if (null !== ($inputData = $httpData->optArray('input', null, null))) {
			try {
				$siInput = SiInput::parse($inputData);
			} catch (CorruptedSiDataException $e) {
				throw new BadRequestException(previous: $e);
			}
		}

		return new SiZoneCall($siInput, $zoneControlName);
	}

	public function jsonSerialize(): mixed {
		return [
			'zoneControlName' => $this->zoneControlName,
			'input' => $this->input
		];
	}
}