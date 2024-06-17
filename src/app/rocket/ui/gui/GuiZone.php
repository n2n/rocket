<?php

namespace rocket\ui\gui;

use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\gui\control\ZoneGuiControlsMap;
use rocket\ui\si\SiZone;
use rocket\op\ei\manage\api\SiCallResult;
use n2n\web\http\Method;
use rocket\ui\gui\control\GuiControlPath;
use rocket\ui\si\input\SiInputFactory;
use rocket\ui\si\input\SiInput;
use rocket\ui\si\content\SiZoneCall;

class GuiZone {
	private SiZone $siZone;

	function __construct(private Gui $gui, string $title = null, private ?GuiControlMap $zoneGuiControlMap = null) {
		$this->siZone = new SiZone($this->gui->getSiGui(), $title, $this->zoneGuiControlMap?->getSiControls() ?? []);
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