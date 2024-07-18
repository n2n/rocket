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

use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\gui\EiGuiControlName;

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
	function handleSiCall(EiGuiControlName $guiControlPath, N2nContext $n2nContext): SiCallResponse {
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