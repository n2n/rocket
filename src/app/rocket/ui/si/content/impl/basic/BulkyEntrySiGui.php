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
namespace rocket\ui\si\content\impl\basic;

use rocket\ui\si\content\SiGui;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\control\SiControl;
use n2n\util\type\ArgUtils;
use rocket\ui\si\SiPayloadFactory;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\input\SiInputError;
use rocket\ui\si\api\request\SiInput;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\response\SiInputResult;

class BulkyEntrySiGui implements SiGui {

	private $controls = [];
	private $entryControlsIncluded = true;
	
	function __construct(private ?SiFrame $frame, private SiDeclaration $declaration,
			private ?SiValueBoundary $valueBoundary = null) {
	}
	
	/**
	 * {@inheritDoc}
	 * @see SiGui::getTypeName
	 */
	function getTypeName(): string {
		return 'bulky-entry';
	}

	function getDeclaration(): SiDeclaration {
		return $this->declaration;
	}

	function setValueBoundary(?SiValueBoundary $entry): static {
		$this->valueBoundary = $entry;
		return $this;
	}

	function getValueBoundary(): ?SiValueBoundary {
		return $this->valueBoundary;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return BulkyEntrySiGui
	 */
	function setControls(array $controls): static {
		ArgUtils::valArray($controls, SiControl::class);
		$this->controls = $controls;
		return $this;
	}
	
	/**
	 * @return SiControl[]
	 */
	function getControls(): array {
		return $this->controls;
	}
	
	/**
	 * @param bool $entryControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function setEntryControlsIncluded(bool $entryControlsIncluded) {
		$this->entryControlsIncluded = $entryControlsIncluded;
		return $this;
	}
	
	/**
	 * Whether this SiGui will request the entry controls of the entry when it has to be reloaded.
	 * @param bool $entryControlsIncluded
	 * @return bool
	 */
	function getEntryControlsIncluded(bool $entryControlsIncluded) {
		return $this->entryControlsIncluded;
	}


	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): SiInputResult {
		$valueBoundaryInput = $siInput->getValueBoundaryInputs();
		if (count($valueBoundaryInput) > 1) {
			throw new CorruptedSiDataException('BulkyEiGui can not handle multiple SiEntryInputs.');
		}

		foreach ($valueBoundaryInput as $valueBoundaryInput) {
			if ($this->valueBoundary->handleInput($valueBoundaryInput, $n2nContext)) {
				return SiInputResult::valid([$this->valueBoundary]);
			}

			return SiInputResult::error([$this->valueBoundary]);
		}

		throw new CorruptedSiDataException('No ValueBoundaryInputs provided.');
	}


	/**
	 * {@inheritDoc}
	 * @see SiGui::toJsonStruct
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [ 
			'frame' => $this->frame,
			'declaration' => $this->declaration,
			'valueBoundary' => $this->valueBoundary->toJsonStruct($n2nContext),
			'controls' => SiPayloadFactory::createDataFromControls($this->controls),
			'entryControlsIncluded' => $this->entryControlsIncluded
		];
	}
}
