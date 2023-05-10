<?php

namespace rocket\op\cu\util\gui;

use rocket\op\cu\gui\CuMaskedEntry;
use rocket\op\cu\gui\field\CuField;
use rocket\si\meta\SiStructureType;
use rocket\impl\cu\gui\BulkyCuGui;
use rocket\op\cu\gui\CuGui;
use rocket\op\cu\gui\control\CuControl;

class CufBulkyGui implements CufGui {

	private CuMaskedEntry $cuMaskedEntry;
	private BulkyCuGui $bulkyCuGui;

	function __construct(bool $readOnly) {
		$maskId = 'mask-cuf-bulky-gui';
		$typeId = 'type-cuf-bulky-gui';

		$this->cuMaskedEntry = new CuMaskedEntry($maskId, $typeId, 'Unnamed Boundary');
		$this->bulkyCuGui = new BulkyCuGui($readOnly);
		$this->bulkyCuGui->addCuMaskedEntry($this->cuMaskedEntry);
		$this->bulkyCuGui->setSelectedMaskId($maskId);
	}

	function addField(string $propId, string $label, CuField $cuField, string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {
		$this->cuMaskedEntry->structure()->addCuField($propId, $label, $cuField, $helpText, $siStructureType);
		return $this;
	}

	function addControl(CuControl $cuControl): static {
		$this->bulkyCuGui->addCuControl($cuControl);
		return $this;
	}

	function getCuGui(): CuGui {
		return $this->bulkyCuGui;
	}

}
