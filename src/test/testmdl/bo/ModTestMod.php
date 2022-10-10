<?php

namespace testmdl\bo;

use n2n\context\attribute\ThreadScoped;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiSetup;
use rocket\si\control\SiButton;
use rocket\impl\ei\component\cmd\EiCmdNatures;

#[ThreadScoped]
class ModTestMod {

	#[EiSetup]
	private function setup(Eiu $eiu): void {
		$eiu->mask()->addEiCmd(EiCmdNatures::entryCallback(
				SiButton::info('Login'),
				function (Eiu $eiu) {
					return $eiu->factory()->newControlResponse()->redirectToHref('https://n2n.rocks/');
				}));
	}

}