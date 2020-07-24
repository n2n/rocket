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

use rocket\ei\component\prop\PrivilegedEiProp;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldEditable;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\gui\GuiFields;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\util\factory\EifField;
use n2n\validation\plan\impl\Validators;

abstract class EditablePropertyEiPropAdapter extends PropertyDisplayableEiPropAdapter 
		implements PrivilegedEiProp, FieldEiProp, StatelessGuiFieldEditable {
	private $editConfig;

	function isPrivileged(): bool {
		return true;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	protected function getEditConfig() {
		if ($this->editConfig === null) {
			$this->editConfig = new EditConfig();
		}

		return $this->editConfig;
	}

	protected function createConfigurator(): AdaptableEiPropConfigurator {
		return parent::createConfigurator()->addAdaption($this->getEditConfig());
	}
	
	protected function createEifField(Eiu $eiu): EifField {
		$eifField = parent::createEifField($eiu);
		
		if (!$this->getEditConfig()->isReadOnly()) {
			$eifField->setWriter(function ($value) use ($eiu) {
						return $eiu->entry()->writeNativeValue($this, $value);
					});
		}
		
		$eifField->setCopier(function ($value) {
			return $value;
		});
				
		if ($this->getEditConfig()->isMandatory()) {
			$eifField->val(Validators::mandatory());
// 			ValidationMessages::mandatory($eiu->prop()->getLabel())
		}
		return $eifField;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyDisplayableEiPropAdapter::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return GuiFields::stateless($eiu, $this, ($readOnly ? null : $this));
	}

// 	public function loadSiField(Eiu $eiu, SiField $siField) {
// 		$siField->setValue($eiu->field()->getValue());
// 	}
	
// 	public function saveSiField(SiField $siField, Eiu $eiu) {
// 		$eiu->field()->setValue($siField->getValue());
// 	}
}