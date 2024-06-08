<?php

namespace rocket\ui\gui\control;

use rocket\ui\si\control\SiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use n2n\util\uri\Url;

class GuiControlMap {

	/**
	 * @var \rocket\op\gui\control\GuiControlRecord
	 */
	private array $guiControlRecords = [];

	function __construct() {

	}

	function putGuiControl(GuiControlPath $guiControlPath, GuiControl $guiControl,
			ApiControlCallId $apiControlCallId, Url $apiUrl): void {
		$this->guiControlRecords[(string) $guiControlPath] = new \rocket\op\gui\control\GuiControlRecord($guiControlPath, $guiControl,
				$apiUrl, $apiControlCallId);
	}

//	/**
//	 * @return GuiControl[]
//	 */
//	function getGuiControls(): array {
//		return $this->guiControls;
//	}

	/**
	 * @return SiControl[]
	 */
	function createSiControls(): array {
		$siControls = [];
		foreach ($this->guiControlRecords as $guiControlPathStr => $guiControlRecord) {
			$siControls[$guiControlPathStr] = $guiControlRecord->createSiControl();
		}
		return $siControls;
	}

}

class GuiControlRecord {
	function __construct(public readonly GuiControlPath $guiControlPath, public readonly GuiControl $guiControl,
			public readonly Url $apiUrl, public readonly ApiControlCallId $apiControlCallId) {
	}

	function createSiControl(): SiControl {
		return $this->guiControl->toSiControl($this->apiUrl, $this->apiControlCallId);
	}
}