<?php

namespace rocket\op\ei\manage\gui\factory\mock;

use rocket\impl\ei\component\prop\adapter\EiPropNatureAdapter;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\EiGuiProp;

class EiPropNatureMock extends EiPropNatureAdapter {

	public ?\Closure $buildEiGuiPropClosure = null;

	function buildEiGuiProp(Eiu $eiu): ?EiGuiProp {
		return $this->buildEiGuiPropClosure?->__invoke($eiu);
	}
}