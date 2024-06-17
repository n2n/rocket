<?php

namespace rocket\ui\gui\control;

use rocket\ui\si\control\SiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use n2n\util\uri\Url;

class GuiControlMap {

	/**
	 * @var GuiControl[]
	 */
	private array $guiControls = [];

	function __construct() {

	}

//	function putGuiControl(string $name, GuiControl $guiControl,
//			ApiControlCallId $apiControlCallId, Url $apiUrl): void {
//		$this->guiControlRecords[(string) $guiControlPath] = new \rocket\op\gui\control\GuiControlRecord($guiControlPath, $guiControl,
//				$apiUrl, $apiControlCallId);
//	}

	function putGuiControl(string $controlName, GuiControl $guiControl): void {
		$this->guiControls[$controlName] = $guiControl;
	}

//	/**
//	 * @return GuiControl[]
//	 */
//	function getGuiControls(): array {
//		return $this->guiControls;
//	}

	/**
	 * @return GuiControl[]
	 */
	function getGuiControls(): array {
		return $this->guiControls;
	}

}

//class GuiControlRecord {
//	function __construct(public readonly GuiControlPath $guiControlPath, public readonly GuiControl $guiControl,
//			public readonly Url $apiUrl, public readonly ApiControlCallId $apiControlCallId) {
//	}
//
//	function createSiControl(): SiControl {
//		return $this->guiControl->getSiControl($this->apiUrl, $this->apiControlCallId);
//	}
//}