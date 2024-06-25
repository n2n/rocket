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

class SiApiCallResponse implements \JsonSerializable {

	private ?SiInputResult $inputResult = null;
	private ?SiCallResponse $callResult = null;
	private ?SiFieldCallResponse $fieldCallResponse = null;
	private ?SiGetResponse $getResult = null;
	private ?SiValResponse $valResponse = null;

	public function getInputResult(): ?SiInputResult {
		return $this->inputResult;
	}

	function setInputResult(SiInputResult $inputResult): void {
		$this->inputResult = $inputResult;
	}

	public function getCallResponse(): ?SiCallResponse {
		return $this->callResult;
	}

	public function setCallResponse(?SiCallResponse $callResult): static {
		$this->callResult = $callResult;
		return $this;
	}

	public function getGetResponse(): ?SiGetResponse {
		return $this->getResult;
	}

	public function setGetResponse(?SiGetResponse $getResult): static {
		$this->getResult = $getResult;
		return $this;
	}

	function getValResponse(): ?SiValResponse {
		return $this->valResponse;
	}

	function setValResponse(?SiValResponse $valResponse): static {
		$this->valResponse = $valResponse;
		return $this;
	}

	public function getFieldCallResponse(): ?SiFieldCallResponse {
		return $this->fieldCallResponse;
	}

	function setFieldCallResponse(?SiFieldCallResponse $fieldCallResponse): static {
		$this->fieldCallResponse = $fieldCallResponse;
		return $this;
	}

	function jsonSerialize(): mixed {
		return [
			'callResult' => $this->callResult,
			'getResult' => $this->getResult
		];
	}
}