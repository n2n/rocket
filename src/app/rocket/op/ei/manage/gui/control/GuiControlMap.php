<?php

namespace rocket\op\ei\manage\gui\control;

use rocket\si\control\SiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use n2n\util\uri\Url;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\frame\EiFrame;

class GuiControlMap {

	/**
	 * @var GuiControlRecord[]
	 */
	private array $guiControlRecords = [];

	function __construct() {

	}

	function putGuiControl(GuiControlPath $guiControlPath, GuiControl $guiControl,
			ApiControlCallId $apiControlCallId, Url $apiUrl): void {
		$this->guiControlRecords[(string) $guiControlPath] = new GuiControlRecord($guiControlPath, $guiControl,
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