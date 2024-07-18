<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */

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

	function putGuiControl(GuiControlKey $guiControlKey, GuiControl $guiControl): void {
		$this->guiControls[(string) $guiControlKey] = $guiControl;
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