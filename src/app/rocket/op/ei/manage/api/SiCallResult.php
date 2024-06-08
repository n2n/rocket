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
namespace rocket\op\ei\manage\api;

use rocket\si\input\SiInputError;
use rocket\ui\si\control\SiCallResponse;
use rocket\si\input\SiInputResult;

class SiCallResult implements \JsonSerializable {
	private $inputError;
	private $callResponse;
	private $inputResult;
	
	/**
	 * @param SiInputError $inputError
	 * @param SiCallResponse $callResponse
	 */
	private function __construct(?SiInputError $inputError, ?SiCallResponse $callResponse, ?SiInputResult $inputResult) {
		$this->inputError = $inputError;
		$this->callResponse = $callResponse;
		$this->inputResult = $inputResult;
	}
	
	/**
	 * @param SiInputError $inputError
	 * @return \rocket\op\ei\manage\api\SiCallResult
	 */
	static function fromInputError(SiInputError $inputError) {
		return new SiCallResult($inputError, null, null);
	}
	
	/**
	 * @param SiCallResponse $callResponse
	 * @return \rocket\op\ei\manage\api\SiCallResult
	 */
	static function fromCallResponse(SiCallResponse $callResponse, ?SiInputResult $inputResult) {
		return new SiCallResult(null, $callResponse, $inputResult);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'inputError' => $this->inputError,
			'callResponse' => $this->callResponse,
			'inputResult' => $this->inputResult
		];
	}
}
