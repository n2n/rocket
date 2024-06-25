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
namespace rocket\ui\si\api\response;

use rocket\ui\si\input\SiInputResult;
use rocket\ui\si\input\SiInputError;
use n2n\util\type\ArgUtils;

class SiCallResult implements \JsonSerializable {
	private $inputError;
	private $callResponse;
	private $inputResult;

	/**
	 * @param SiCallResponse|null $callResponse
	 * @param SiInputResult|null $inputResult
	 */
	private function __construct(?SiCallResponse $callResponse, ) {
		$this->callResponse = $callResponse;
		$this->inputResult = $inputResult;
	}
	
	/**
	 * @param SiInputError $inputResult
	 * @return \rocket\op\ei\manage\api\SiCallResult
	 */
	static function fromInputError(SiInputResult $inputResult) {
		ArgUtils::assertTrue(!$inputResult->isValid());
		return new SiCallResult(null, $inputResult);
	}
	
	/**
	 * @param SiCallResponse $callResponse
	 * @return \rocket\op\ei\manage\api\SiCallResult
	 */
	static function fromCallResponse(SiCallResponse $callResponse, ?\rocket\ui\si\input\SiInputResult $inputResult) {
		return new SiCallResult(null, $callResponse, $inputResult);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'callResponse' => $this->callResponse,
			'inputResult' => $this->inputResult
		];
	}
}
