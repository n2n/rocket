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

namespace rocket\impl\cu\gui;

use n2n\util\uri\Url;
use rocket\ui\si\content\SiGui;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\content\impl\basic\BulkyEntrySiGui;
use rocket\ui\si\meta\SiDeclaration;
use rocket\op\cu\gui\CuMaskedEntry;
use rocket\op\cu\gui\control\CuControl;
use rocket\ui\si\content\SiValueBoundary;
use rocket\op\cu\gui\CuGui;
use rocket\ui\si\api\request\SiInput;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\op\cu\gui\control\CuControlCallId;
use rocket\ui\si\api\response\SiCallResponse;
use n2n\core\container\N2nContext;
use rocket\op\cu\util\Cuu;

class BulkyCuGui implements CuGui {

	private ?string $selectedMaskId = null;
	/**
	 * @var array<CuMaskedEntry>
	 */
	private array $cuMaskedEntries = [];
	/**
	 * @var array<CuControl>
	 */
	private array $cuControls = [];

	/**
	 * @var array<SiValueBoundary>
	 */
	private array $inputSiValueBoundaries = [];

	function __construct(private bool $readOnly) {

	}

	function setSelectedMaskId(?string $maskId): static {
		if (!isset($this->cuMaskedEntries[$maskId])) {
			throw new IllegalStateException('Unknown mask id: ' . $maskId);
		}

		$this->selectedMaskId = $maskId;
		return $this;
	}

	function addCuMaskedEntry(CuMaskedEntry $cuMaskedEntry): static {
		$this->cuMaskedEntries[$cuMaskedEntry->getMaskId()] = $cuMaskedEntry;
		return $this;
	}

	function addCuControl(CuControl $cuControl): static {
		$this->cuControls[$cuControl->getId()] = $cuControl;
		return $this;
	}

	/**
	 * @return SiValueBoundary[]
	 */
	function getInputSiValueBoundaries(): array {
		return $this->inputSiValueBoundaries;
	}

	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): ?SiInputError {
		$entryInputs = $siInput->getValueBoundaryInputs();
		if (count($entryInputs) > 1) {
			throw new CorruptedSiDataException('BulkyEntrySiGui can not handle multiple SiEntryInputs.');
		}

		foreach ($entryInputs as $entryInput) {
			$maskId = $entryInput->getSelectedTypeId();
			if (!isset($this->cuMaskedEntries[$maskId])) {
				throw new CorruptedSiDataException('BulkyEntrySiGui has no entry of maskId: ' . $maskId);
			}

			$this->setSelectedMaskId($maskId);

			if ($this->cuMaskedEntries[$maskId]->handleSiEntryInput($entryInput, $n2nContext)) {
				$this->inputSiValueBoundaries = [$this->createSiValueBoundary()];
				return null;
			}

			return new SiInputError([$this->createSiValueBoundary()]);
		}

		throw new IllegalStateException();
	}

	private function createSiValueBoundary(): SiValueBoundary {
		$siStyle = new SiStyle(true, $this->readOnly);
		$siValueBoundary = new SiValueBoundary($siStyle);
		foreach ($this->cuMaskedEntries as $id => $cuMaskedEntry) {
			$siValueBoundary->putEntry($id, $cuMaskedEntry->getSiEntry());
		}
		$siValueBoundary->setSelectedTypeId($this->selectedMaskId);
		return $siValueBoundary;
	}

	function handleCall(CuControlCallId $cuControlCallId, Cuu $cuu): SiCallResponse {
		$controlId = $cuControlCallId->getControlId();

		if (isset($this->cuControls[$controlId])) {
			return $this->cuControls[$controlId]->handle(new Cuu($cuu, $this->cuMaskedEntries[$this->selectedMaskId]?->getCuEntry()));
		}

		throw new CorruptedSiDataException('Unknown control id: ' . $controlId);
	}

	function toSiGui(?Url $zoneApiUrl = null): SiGui {
		IllegalStateException::assertTrue(empty($this->cuControls) || $zoneApiUrl !== null,
				'Zone api url not available, but controls of this gui requires one.');

		$siStyle = new SiStyle(true, $this->readOnly);
		$siDeclaration = new SiDeclaration($siStyle);
		$siValueBoundary = new SiValueBoundary($siStyle);
		foreach ($this->cuMaskedEntries as $id => $cuMaskedEntry) {
			$siDeclaration->addMaskDeclaration($cuMaskedEntry->getSiMask());
			$siValueBoundary->putEntry($id, $cuMaskedEntry->getSiEntry());
		}
		$siValueBoundary->setSelectedTypeId($this->selectedMaskId);

		$siGui = new BulkyEntrySiGui(null, $siDeclaration, $siValueBoundary);

		$siControls = [];
		foreach ($this->cuControls as $cuControl) {
			$siControls[] = $cuControl->toSiControl($zoneApiUrl, new CuControlCallId($cuControl->getId()));
		}

		$siGui->setControls($siControls);

		return $siGui;
	}

}