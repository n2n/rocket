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
namespace rocket\impl\ei\component\prop\adapter;

use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\util\Eiu;
use n2n\util\type\TypeConstraint;
use rocket\op\ei\manage\gui\EiGuiField;
use rocket\op\ei\util\factory\EifField;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfigTrait;
use rocket\ui\gui\field\BackableGuiField;

abstract class DisplayablePropertyEiPropNatureAdapter extends EiPropNatureAdapter
		implements PropertyEiPropNature, EiGuiField {
	use DisplayConfigTrait, PropertyAdapter;

	// EiField
	
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		return $this->createEifField($eiu)->toEiField();
	}
	
	protected function createEifField(Eiu $eiu): EifField {
		return $eiu->factory()
				->newField($this->getEiFieldTypeConstraint(), function () use ($eiu) {
					return $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
				});
	}	

	private function getEiFieldTypeConstraint(): ?TypeConstraint {
		if (null !== ($accessProxy = $this->getPropertyAccessProxy())) {
			return $accessProxy->getGetterConstraint()->getLenientCopy();
		}
		
		return null;
	}
	
	// GuiProp
	
	function buildGuiProp(Eiu $eiu): ?EiGuiProp {
		return $eiu->factory()->newGuiProp(function (Eiu $eiu) {
			return $this->getDisplayConfig()->buildGuiPropSetup($eiu, $this);
		})->toEiGuiProp();
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return $this->createOutEifGuiField($eiu)->toGuiField();
	}
	
	protected function createOutGuiField(Eiu $eiu): BackableGuiField {
		throw new UnsupportedOperationException(get_class($this)
				. ' must implement either'
				. ' createEifGuiField(Eiu $eiu, bool $readOnly): EifGuiField or'
				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.');
	}
}
