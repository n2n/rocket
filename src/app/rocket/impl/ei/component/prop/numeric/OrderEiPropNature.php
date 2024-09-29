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

use n2n\impl\persistence\orm\property\IntEntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\numeric\conf\OrderConfig;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\ui\si\content\impl\SiFields;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\impl\ei\component\prop\numeric\component\OrderEiModificator;
use rocket\ui\gui\ViewMode;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\BackableGuiField;

class OrderEiPropNature extends IntegerEiPropNature {
    const ORDER_INCREMENT = 10;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy);

		$this->getDisplayConfig()->changeDefaultDisplayedViewModes(ViewMode::all(), false);
	}

	function setup(Eiu $eiu): void {
		$this->setMandatory(false);
		$eiuMask = $eiu->mask();
		$eiuMask->addMod(new OrderEiModificator($this, $eiu->prop()->getPath()));
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof ScalarEntityProperty
				|| $entityProperty instanceof IntEntityProperty;
	}
	
	function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		return GuiFields::out(SiFields::stringOut($eiu->field()->getValue()));
	}

	public function getSortItem() {
		return new SimpleSortProp($this->getEntityProperty()->getName(), $this->getLabelLstr());
	}

	public function buildInGuiField(Eiu $eiu): ?BackableGuiField {
//		$siField = SiFields::numberIn($eiu->field()->getValue())
//				->setMandatory($this->isMandatory())
//				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());

		return GuiFields::numberIn($this->isMandatory())->setValue($eiu->field()->getValue());
//		return $eiu->factory()->newGuiField($siField)->setSaver(function () use ($siField, $eiu) {
//			$eiu->field()->setValue($siField->getValue());
//		});
	}

	public function getFilterProp() {
// 		return new StringFilterProp($this->getEntityProperty()->getName(), $this->getLabel(),
// 				FilterPropAdapter::createOperatorOptions($n2nContext->getN2nLocale()));
	}
}
