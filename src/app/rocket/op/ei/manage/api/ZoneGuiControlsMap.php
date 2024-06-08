<?php

namespace rocket\op\ei\manage\api;

use rocket\ui\gui\control\GuiControl;
use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\ui\si\control\SiCallResponse;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\EiGuiDeclaration;

class ZoneGuiControlsMap {
	/**
	 * @var GuiControl[]
	 */
	private array $guiControls = [];

	function __construct(private Url $apiUrl, array $guiControls = []) {
		ArgUtils::valArray($guiControls, GuiControl::class);
		foreach ($guiControls as $guiControl) {
			$this->addGuiControl($guiControl);
		}
	}

	function addGuiControl(GuiControl $guiControl): void {
		$this->guiControls[$guiControl->getId()] = $guiControl;
	}

	function createSiControls(): array {
		$siControls = [];
		foreach ($this->guiControls as $id => $guiControl) {
			$siControls[$id] = $guiControl->toSiControl($this->apiUrl, new ZoneApiControlCallId([$id]));
		}
		return $siControls;
	}

	/**
	 * @param ZoneApiControlCallId $zoneControlCallId
	 * @param EiFrame $eiFrame
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param array $inputEiEntries
	 * @return SiCallResponse
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiCall(ZoneApiControlCallId $zoneControlCallId, EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration,
			array $inputEiEntries): SiCallResponse {
		$ids = $zoneControlCallId->toArray();

		$id = array_shift($ids);
		foreach ($this->guiControls as $guiControl) {
			if ($guiControl->getId() !== $id) {
				continue;
			}

			while (!empty($ids) && $guiControl !== null) {
				$id = array_shift($ids);
				$guiControl = $guiControl->getChildById($id);
			}

			if ($guiControl !== null) {
				return $guiControl->handle($eiFrame, $eiGuiDeclaration, $inputEiEntries);
			}
		}

		throw new CorruptedSiInputDataException('Could not find matching GuiControl for ZoneApiControlCallId: '
				. $zoneControlCallId);
	}
}