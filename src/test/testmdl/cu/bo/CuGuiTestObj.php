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

namespace testmdl\cu\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\cmd\EiCmdNatures;
use rocket\impl\ei\manage\gui\GuiControls;
use rocket\ui\si\control\SiButton;
use rocket\ui\si\control\SiIconType;
use testmdl\cu\controller\CuGuiTestController;

#[EiType]
#[EiMenuItem('Holeradio', groupName: 'Super Duper Guper', groupKey: 'super-duper')]
class CuGuiTestObj {
	private int $id;

	#[EiSetup]
	static function eiSetup(Eiu $eiu): void {
		$eiu->mask()->addCmd(EiCmdNatures::callback()
				->setController(fn (Eiu $eiu) => $eiu->lookup(CuGuiTestController::class))
				->addEntryGuiControl(function (Eiu $eiu) {
					return GuiControls::ref('cu-id', $eiu->frame()->getCmdUrl($eiu->cmd()->getEiCmdPath()),
							SiButton::warning('Warning', SiIconType::ICON_BAN));
				}));
	}

}