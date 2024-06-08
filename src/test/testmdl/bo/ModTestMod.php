<?php

namespace testmdl\bo;

use n2n\context\attribute\ThreadScoped;
use rocket\op\ei\util\Eiu;
use rocket\attribute\impl\EiSetup;
use rocket\ui\si\control\SiButton;
use rocket\impl\ei\component\cmd\EiCmdNatures;
use rocket\impl\ei\manage\gui\GuiControls;

#[ThreadScoped]
class ModTestMod {

	#[EiSetup]
	private function setup(Eiu $eiu): void {
		$eiu->mask()->addCmd(EiCmdNatures::callback()
				->addEntryGuiControl(function (Eiu $eiu) {
					return GuiControls::href('hc2', 'https://www.hippocrocodiles.com',
							SiButton::secondary('login'));
				}));

	}

}