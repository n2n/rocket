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

use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\control\SiControl;
use n2n\util\type\ArgUtils;
use rocket\ui\si\SiPayloadFactory;
use n2n\core\container\N2nContext;

class SiGetInstructionResult {
	/**
	 * @var SiDeclaration|null
	 */
	private $declaration = null;
	/**
	 * @var SiControl[]|null
	 */
	private $generalControls = null;
	/**
	 * @var SiValueBoundary|null
	 */
	private $valueBoundary = null;
	/**
	 * @var SiPartialContent|null
	 */
	private $partialContent;
	
	/**
	 * 
	 */
	function __construct() {
	}
	
	/**
	 * @return SiDeclaration|null
	 */
	public function getDeclaration(): ?SiDeclaration {
		return $this->declaration;
	}

	/**
	 * @param SiDeclaration|null $declaration
	 */
	public function setDeclaration(?SiDeclaration $declaration): void {
		$this->declaration = $declaration;
	}
	
	/**
	 * @return SiControl[]|null
	 */
	public function getGeneralControls(): ?array {
		return $this->generalControls;
	}
	
	/**
	 * @param SiControl[]|null $controls
	 */
	public function setGeneralControls(?array $controls): void {
		ArgUtils::valArray($controls, SiControl::class);
		$this->generalControls = $controls;
	}

	public function getValueBoundary(): ?SiValueBoundary {
		return $this->valueBoundary;
	}

	public function setValueBoundary(?SiValueBoundary $valueBoundary): void {
		$this->valueBoundary = $valueBoundary;
	}

	/**
	 * @return SiPartialContent|null
	 */
	public function getPartialContent(): ?SiPartialContent {
		return $this->partialContent;
	}

	/**
	 * @param SiPartialContent|null $partialContent
	 */
	public function setPartialContent(?SiPartialContent $partialContent): void {
		$this->partialContent = $partialContent;
	}

	/**
	 * @return SiValueBoundary[]
	 */
	function getAllValueBoundaries(): array {
		$valueBoundaries = [];

		if ($this->valueBoundary !== null) {
			$valueBoundaries[] = $this->valueBoundary;
		}

		if ($this->partialContent !== null) {
			array_push($valueBoundaries, ...$this->partialContent->getValueBoundaries());
		}

		return $valueBoundaries;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function toJsonStruct(N2nContext $n2nContext): mixed {
		return [
			'declaration' => $this->declaration,
			'generalControls' => ($this->generalControls !== null ? SiPayloadFactory::createDataFromControls($this->generalControls) : null),
			'entry' => $this->valueBoundary->toJsonStruct($n2nContext),
			'partialContent' => $this->partialContent
		];
	}	
}
