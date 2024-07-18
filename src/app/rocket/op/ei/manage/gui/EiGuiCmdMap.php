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


namespace rocket\op\ei\manage\gui;

use rocket\ui\si\control\SiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use n2n\util\uri\Url;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\manage\gui\EiGuiCmd;
use rocket\op\ei\manage\gui\EiGuiCmdWrapper;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\control\GuiControlMap;
use rocket\op\ei\util\Eiu;
use n2n\core\container\N2nContext;
use rocket\ui\gui\control\GuiControlKey;
use n2n\util\type\ArgUtils;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\component\InvalidEiConfigurationException;
use rocket\op\ei\manage\entry\EiEntry;

class EiGuiCmdMap {

	/**
	 * @var EiGuiCmdWrapper[]
	 */
	private array $eiGuiCmdWrappers = [];

	function __construct(private EiGuiDefinition $eiGuiDefinition) {

	}

//	function putGuiControl(string $name, GuiControl $guiControl,
//			ApiControlCallId $apiControlCallId, Url $apiUrl): void {
//		$this->guiControlRecords[(string) $guiControlPath] = new \rocket\op\gui\control\GuiControlRecord($guiControlPath, $guiControl,
//				$apiUrl, $apiControlCallId);
//	}

	function putEiGuiCmd(EiCmdPath $eiCmdPath, EiGuiCmd $eiGuiCmd): void {
		$this->eiGuiCmdWrappers[(string) $eiCmdPath] = new EiGuiCmdWrapper($eiCmdPath, $eiGuiCmd);
	}


	function createGeneralGuiControlsMap(EiFrame $eiFrame): GuiControlMap {
		$guiControlsMap = new GuiControlMap();

		foreach ($this->eiGuiCmdWrappers as $eiGuiCmdWrapper) {
			foreach ($eiGuiCmdWrapper->createGeneralGuiControls($this->eiGuiDefinition, $eiFrame)
					 as $key => $guiControl) {
				$guiControlsMap->putGuiControl($eiGuiCmdWrapper->getEiCmdPath()->ext($key)->toGuiControlKey(),
						$guiControl);
			}
		}

		return $guiControlsMap;
	}

	function createEntryGuiControlsMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiControlMap {
		$guiControlsMap = new GuiControlMap();

		foreach ($this->eiGuiCmdWrappers as $eiGuiCmdWrapper) {
			foreach ($eiGuiCmdWrapper->createEntryGuiControls($this->eiGuiDefinition, $eiFrame, $eiEntry)
					 as $key => $guiControl) {
				$guiControlsMap->putGuiControl($eiGuiCmdWrapper->getEiCmdPath()->ext($key)->toGuiControlKey(),
						$guiControl);
			}
		}

		return $guiControlsMap;
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
	function getEiGuiCmdWrappers(): array {
		return $this->eiGuiCmdWrappers;
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