<?php

namespace rocket\cu\util\gui;

use rocket\cu\gui\CuMaskedEntry;
use n2n\util\HashUtils;
use rocket\cu\gui\field\CuField;
use rocket\si\meta\SiStructureType;
use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\cu\gui\impl\BulkyCuGui;
use rocket\cu\gui\CuGui;
use rocket\cu\gui\control\impl\CuControls;
use rocket\cu\gui\control\CuControl;

class CufBulkyGui implements CufGui {

	private CuMaskedEntry $cuMaskedEntry;
	private BulkyCuGui $bulkyCuGui;

	function __construct(bool $readOnly) {
		$maskId = 'mask-' . HashUtils::base36Uniqid(false);
		$typeId = 'type-' . HashUtils::base36Uniqid(false);

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
