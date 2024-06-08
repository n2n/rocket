<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use rocket\attribute\impl\EiModCallback;
use rocket\ui\si\control\SiButton;
use rocket\impl\ei\component\cmd\EiCmdNatures;

#[EiType]
#[EiModCallback(ModTestMod::class)]
class ModTestObj {

	public int $id;
	public string $string;

	#[EiSetup]
	private static function setup(Eiu $eiu): void {
		$eiu->mask()->addCmd(EiCmdNatures::callback()->addGeneralGuiControl(function (Eiu $eiu) {
			return $eiu->f()->gc()->newCallback('hc', SiButton::danger('Super duper danger!'),
					function (Eiu $eiu) {
						return $eiu->factory()->newControlResponse()->redirectToHref('https://n2n.rocks/');
					});
		}));
	}
}