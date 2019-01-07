<?php
namespace rocket\impl\ei\component\modificator\constraint;

use rocket\impl\ei\component\modificator\adapter\IndependentEiModificatorAdapter;
use rocket\ei\util\Eiu;

class UniqueEiModificator extends IndependentEiModificatorAdapter {
	
	
	function setupEiGui(Eiu $eiu) {
		$eiu->gui()->initWithUiCallback($viewFactory, $eiPropPaths);
	}
}
