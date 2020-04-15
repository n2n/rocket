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

use rocket\ei\manage\entry\EiField;
use rocket\ei\component\prop\PrivilegedEiProp;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldEditable;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\gui\GuiFields;
use n2n\validation\lang\ValidationMessages;
use rocket\impl\ei\component\prop\adapter\entry\EiFields;
use rocket\ei\component\prop\FieldEiProp;
use rocket\impl\ei\component\prop\adapter\entry\StatelessEiFieldCopier;
use rocket\impl\ei\component\prop\adapter\entry\StatelessEiFieldValidator;
use rocket\impl\ei\component\prop\adapter\entry\StatelessEiFieldWriter;

abstract class EditablePropertyEiPropAdapter extends PropertyDisplayableEiPropAdapter 
		implements PrivilegedEiProp, 
				FieldEiProp, StatelessEiFieldWriter, StatelessEiFieldValidator, StatelessEiFieldCopier,
				StatelessGuiFieldEditable {
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
	
	public function buildEiField(Eiu $eiu): ?EiField {
		return EiFields::statless($eiu, $this->getEiFieldTypeConstraint(), $this, $this, $this, $this);
	}
	
	public function writeEiFieldValue(Eiu $eiu, $value) {
		$eiu->entry()->writeNativeValue($this, $value);
	}
	
	public function copyEiFieldValue(Eiu $eiu, $value, Eiu $copyEiu) {
		return $value;
	}
	
	private function checkMandatory($eiFieldValue) {
		if (!$this->getEditConfig()->isMandatory() /*|| $eiObject->isDraft()*/) {
			return true;
		}
		
		if (is_array($eiFieldValue)) {
			return !empty($eiFieldValue);
		}
		
		return $eiFieldValue !== null; 
	}
	
	public function acceptsEiFieldValue(Eiu $eiu, $eiFieldValue): bool {
		return $this->checkMandatory($eiFieldValue);
	}
	
	public function validateEiFieldValue(Eiu $eiu, $eiFieldValue, EiFieldValidationResult $validationResult) {
		if (!$this->checkMandatory($eiFieldValue)) {
			$validationResult->addError(ValidationMessages::mandatory($eiu->prop()->getLabel()));
		}
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