<?php
namespace rocket\impl\ei\component\mod\constraint;

use rocket\impl\ei\component\mod\adapter\IndependentEiModificatorAdapter;
use rocket\op\ei\util\Eiu;

class UniqueEiModificator extends IndependentEiModificatorAdapter {
	
	
	function setupEiGuiFrame(Eiu $eiu) {
		$eiu->guiFrame()->initWithUiCallback($viewFactory, $eiPropPaths);
	}
}
