<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiSetup;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiModCallback;
use rocket\si\control\SiButton;
use rocket\impl\ei\component\cmd\EiCmdNatures;

#[EiType]
#[EiModCallback(ModTestMod::class)]
class ModTestObj {

	public int $id;
	public string $string;

	#[EiSetup]
	private static function setup(Eiu $eiu): void {
		$eiu->mask()->addCmd(EiCmdNatures::generalCallback(
				function (Eiu $eiu) {
					return SiButton::danger('Super duper danger!');
				},
				function (Eiu $eiu) {
					return $eiu->factory()->newControlResponse()->redirectToHref('https://n2n.rocks/');
				}));
	}
}