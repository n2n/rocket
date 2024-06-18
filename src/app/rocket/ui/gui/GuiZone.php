<?php

namespace rocket\ui\gui;

use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\si\SiZone;

class GuiZone {
	private SiZone $siZone;

	function __construct(private Gui $gui, string $title = null, array $siBreadcrumbs = [], private ?GuiControlMap $zoneGuiControlMap = null) {
		$this->siZone = new SiZone($this->gui->getSiGui(), $title, $siBreadcrumbs, $this->zoneGuiControlMap?->getSiControls() ?? []);
	}

	function getSiZone(): SiZone {
		return $this->siZone;
	}

//	function handleSiCall(SiZoneCall $siZoneCall): ?SiCallResult {
//		$zoneControlPath = $this->cu->getParamPost('zoneControlPath');
//		if (!($this->cu->getRequest()->getMethod() === Method::POST && null !== $zoneControlPath)) {
//			return null;
//		}
//
//		$zoneControlPath = GuiControlPath::create($zoneControlPath);
//
//		$siInputResult = null;
//		if (null !== ($entryInputMapsParam = $this->cu->getParamPost('entryInputMaps'))) {
//			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
//			if (null !== ($siInputError = $gui->handleSiInput($siInput))) {
//				return SiCallResult::fromInputError($siInputError);
//			}
//
//			$siInputResult = new \rocket\ui\si\input\SiInputResult($gui->getInputSiValueBoundaries());
//		}
//
//		return SiCallResult::fromCallResponse(
//				$zoneGuiControlsMap->handleSiCall($zoneControlPath),
//				$siInputResult);
//	}


}