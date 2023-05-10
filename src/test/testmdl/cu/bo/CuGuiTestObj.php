<?php

namespace testmdl\cu\bo;

use rocket\attribute\EiType;
use rocket\attribute\MenuItem;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\cmd\EiCmdNatures;
use rocket\impl\ei\manage\gui\GuiControls;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;
use testmdl\cu\controller\CuGuiTestController;

#[EiType]
#[MenuItem('Holeradio', groupName: 'Super Duper Guper', groupKey: 'super-duper')]
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