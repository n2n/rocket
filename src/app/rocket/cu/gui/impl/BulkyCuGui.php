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

class BulkyCuGui implements CuGui {

	private ?string $selectedMaskId = null;
	/**
	 * @var array<CuMaskedEntry>
	 */
	private array $cuMaskedEntries = [];
	private array $cuControls = [];

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

		$controls = array_map(
				fn ($c) => $c->toSiControl($zoneApiUrl, ZoneApiControlCallId::create([$c->getId()])),
				$this->cuControls);

		$siGui->setControls($controls);

		return $siGui;
	}

}