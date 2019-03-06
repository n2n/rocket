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
namespace rocket\impl\ei\component\prop\numeric;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\impl\ei\component\prop\numeric\conf\OrderEiPropConfigurator;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\impl\persistence\orm\property\IntEntityProperty;

class OrderEiProp extends IntegerEiProp {
	const ORDER_INCREMENT = 10;
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$this->getDisplayConfig()->setListReadModeDefaultDisplayed(false);
		
		return new OrderEiPropConfigurator($this);
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof ScalarEntityProperty
				|| $entityProperty instanceof IntEntityProperty;
	}
	
	public function createUiComponent(HtmlView $view, Eiu $eiu) {
		return $view->getHtmlBuilder()->getEsc($eiu->field()->getValue(EiPropPath::from($this)));
	}

	public function getSortItem() {
		return new SimpleSortProp($this->getEntityProperty()->getName(), $this->getLabelLstr());
	}

	public function createMag(Eiu $eiu): Mag {
		return new NumericMag($this->getLabelLstr(), null, $this->isMandatory($eiu), 
				null, null, 0, null, array('placeholder' => $this->getLabelLstr()));
	}

	public function getFilterProp() {
// 		return new StringFilterProp($this->getEntityProperty()->getName(), $this->getLabel(),
// 				FilterPropAdapter::createOperatorOptions($n2nContext->getN2nLocale()));
	}
}
