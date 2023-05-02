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

class BulkyCuGui {



	function putCuMaskEntry(string $id, CuMaskedEntry $cuMaskedEntry): static {
		$this->cuMaskEntry($id, $cuMaskedEntry);
	}


	function addControl(GuiControl $control): static {
		$this->guiControls[$control->getId()] = $control;
		return $this;
	}

	function toSiGui(Url $zoneApiUrl = null): SiGui {
		IllegalStateException::assertTrue(empty($this->guiControls) || $zoneApiUrl !== null,
				'Zone api url not available, but controls of this gui requires one.');

		$controls = array_map(
				fn ($c) => $c->toSiControl($zoneApiUrl, ZoneApiControlCallId::create($c)),
				$this->guiControls);

		$siGui = new BulkyEntrySiGui(null, new SiDeclaration($this->style, [$this->siMaskDeclaration]), $this->siValueBoundary);
		$siGui->setControls($controls);

		return $siGui;
	}

}