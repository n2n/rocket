<?php

namespace rocket\cu\gui\impl;

use rocket\ei\manage\gui\control\GuiControl;
use n2n\util\uri\Url;
use rocket\si\content\SiGui;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\api\ZoneApiControlCallId;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\meta\SiDeclaration;
use rocket\cu\gui\CuMaskedEntry;
use rocket\cu\gui\control\CuControl;
use rocket\si\content\SiValueBoundary;
use rocket\si\meta\SiStyle;
use rocket\cu\gui\CuGui;
use rocket\si\input\SiInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\cu\gui\control\CuControlCallId;
use rocket\si\control\SiCallResponse;
use rocket\si\input\SiInputError;
use n2n\core\container\N2nContext;
use rocket\cu\util\Cuu;

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
		$entryInputs = $siInput->getEntryInputs();
		if (count($entryInputs) > 1) {
			throw new CorruptedSiInputDataException('BulkyEntrySiGui can not handle multiple SiEntryInputs.');
		}

		foreach ($entryInputs as $entryInput) {
			$maskId = $entryInput->getMaskId();
			if (!isset($this->cuMaskedEntries[$maskId])) {
				throw new CorruptedSiInputDataException('BulkyEntrySiGui has no entry of maskId: ' . $maskId);
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
		$siValueBoundary->setSelectedMaskId($this->selectedMaskId);
		return $siValueBoundary;
	}

	function handleCall(CuControlCallId $cuControlCallId, Cuu $cuu): SiCallResponse {
		$controlId = $cuControlCallId->getControlId();

		if (isset($this->cuControls[$controlId])) {
			return $this->cuControls[$controlId]->handle($cuu);
		}

		throw new CorruptedSiInputDataException('Unknown control id: ' . $controlId);
	}

	function toSiGui(Url $zoneApiUrl = null): SiGui {
		IllegalStateException::assertTrue(empty($this->cuControls) || $zoneApiUrl !== null,
				'Zone api url not available, but controls of this gui requires one.');

		$siStyle = new SiStyle(true, $this->readOnly);
		$siDeclaration = new SiDeclaration($siStyle);
		$siValueBoundary = new SiValueBoundary($siStyle);
		foreach ($this->cuMaskedEntries as $id => $cuMaskedEntry) {
			$siDeclaration->addMaskDeclaration($cuMaskedEntry->getSiMaskDeclaration());
			$siValueBoundary->putEntry($id, $cuMaskedEntry->getSiEntry());
		}
		$siValueBoundary->setSelectedMaskId($this->selectedMaskId);

		$siGui = new BulkyEntrySiGui(null, $siDeclaration, $siValueBoundary);

		$siControls = [];
		foreach ($this->cuControls as $cuControl) {
			$siControls[] = $cuControl->toSiControl($zoneApiUrl, new CuControlCallId($cuControl->getId()));
		}

		$siGui->setControls($siControls);

		return $siGui;
	}

}