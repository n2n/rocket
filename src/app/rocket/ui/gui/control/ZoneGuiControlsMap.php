<?php

namespace rocket\ui\gui\control;

use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use n2n\core\container\N2nContext;

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

	function getSiControls(): array {
		$siControls = [];
		foreach ($this->guiControls as $id => $guiControl) {
			$siControls[$id] = $guiControl->getSiControl($this->apiUrl, new ZoneApiControlCallId([$id]));
		}
		return $siControls;
	}

	/**
	 * @param ZoneApiControlCallId $zoneControlCallId
	 * @param EiFrame $eiFrame
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param array $inputEiEntries
	 * @return SiCallResponse
	 * @throws CorruptedSiDataException
	 */
	function handleSiCall(GuiControlPath $guiControlPath, N2nContext $n2nContext): SiCallResponse {
		$ids = $guiControlPath->toArray();

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
				return $guiControl->handleCall($n2nContext);
			}
		}

		throw new CorruptedSiDataException('Could not find matching GuiControl for ZoneApiControlCallId: '
				. $guiControlPath->__toString());
	}
}