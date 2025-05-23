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

use n2n\core\container\N2nContext;

class SiApiCallResponse {

	private ?SiInputResult $inputResult = null;
	private ?SiCallResponse $callResponse = null;
	private ?SiFieldCallResponse $fieldCallResponse = null;
	private ?SiGetResponse $getResponse = null;
	private ?SiValResponse $valResponse = null;

	public function getInputResult(): ?SiInputResult {
		return $this->inputResult;
	}

	function setInputResult(SiInputResult $inputResult): void {
		$this->inputResult = $inputResult;
	}

	public function getCallResponse(): ?SiCallResponse {
		return $this->callResponse;
	}

	public function setCallResponse(?SiCallResponse $callResponse): static {
		$this->callResponse = $callResponse;
		return $this;
	}

	public function getGetResponse(): ?SiGetResponse {
		return $this->getResponse;
	}

	public function setGetResponse(?SiGetResponse $getResponse): static {
		$this->getResponse = $getResponse;
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

	function toJsonStruct(N2nContext $n2nContext): mixed {
		return [
			'callResponse' => $this->callResponse,
			'getResponse' => $this->getResponse?->toJsonStruct($n2nContext),
			'valResponse' => $this->valResponse?->toJsonStruct($n2nContext),
			'fieldCallResponse' => $this->fieldCallResponse
		];
	}
}