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
namespace rocket\spec\ei\component\field\impl\numeric;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\spec\ei\component\field\impl\numeric\conf\OrderEiFieldConfigurator;
use rocket\spec\ei\component\EiConfigurator;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;

class OrderEiField extends IntegerEiField {
	const ORDER_INCREMENT = 10;
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new OrderEiFieldConfigurator($this);
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof ScalarEntityProperty;
	}
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo) {
		return $view->getHtmlBuilder()->getEsc($entrySourceInfo->getValue(EiFieldPath::from($this)));
	}

	public function getSortItem() {
		return new SimpleSortField($this->getEntityProperty()->getName(), $this->getLabelLstr());
	}

	public function createMag(string $propertyName, FieldSourceInfo $fieldSourceInfo): Mag {
		return new NumericMag($propertyName, $this->getLabelLstr(), null, $this->isMandatory($fieldSourceInfo), 
				null, null, 0, null, array('placeholder' => $this->getLabelLstr()));
	}

	public function getFilterField() {
// 		return new StringFilterField($this->getEntityProperty()->getName(), $this->getLabel(),
// 				FilterFieldAdapter::createOperatorOptions($n2nContext->getN2nLocale()));
	}
}
