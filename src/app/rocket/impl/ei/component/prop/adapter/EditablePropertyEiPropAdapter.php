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

use n2n\util\type\TypeConstraint;
use n2n\util\type\attrs\Attributes;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\impl\ei\component\prop\adapter\entry\Writable;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\manage\entry\EiField;
use rocket\impl\ei\component\prop\adapter\entry\SimpleEiField;
use rocket\ei\manage\EiObject;
use rocket\ei\component\prop\PrivilegedEiProp;
use rocket\ei\manage\security\privilege\EiPropPrivilege;
use n2n\util\type\ArgUtils;
use rocket\core\model\Rocket;
use rocket\ei\manage\security\EiFieldAccess;
use n2n\util\type\attrs\AttributesException;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\impl\ei\component\prop\adapter\entry\Validatable;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\entry\Copyable;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiProp;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldEditable;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\l10n\Message;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldProxy;

abstract class EditablePropertyEiPropAdapter extends PropertyDisplayableEiPropAdapter implements StatelessGuiFieldEditable, Writable, 
		PrivilegedEiProp, Validatable, Copyable {
	protected $editConfig;

	/**
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	public function getEditConfig() {
		if ($this->editConfig === null) {
			$this->editConfig = new EditConfig();
		}

		return $this->editConfig;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerEditConfig($this->getEditConfig());
		return $eiPropConfigurator;
	}
		
// 	protected function checkForWriteAccess(EiEntry $eiEntry) {
// 		return true;
// // 		$ssPrivilegeConstraint = $eiEntry->getSelectionPrivilegeConstraint();
// // 		if ($ssPrivilegeConstraint === null) return true;
		
// // 		foreach ($ssPrivilegeConstraint->getAccessGrants() as $accessGrant) {
// // 			if (!$accessGrant->isRestricted() || $accessGrant->getAttributesById($this->getId())
// // 					->get(self::ACCESS_WRITING_ALLOWED_KEY, self::ACCESS_WRITING_ALLOWED_DEFAULT)) return true;			
// // 		}
		
// // 		return false;
// 	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		if ($eiu->entry()->isDraft()) {
			return parent::buildEiField($eiu);
		}

		$objectPropertyAccessProxy = $this->getObjectPropertyAccessProxy();
		$constraints = $objectPropertyAccessProxy === null ? TypeConstraint::createSimple('mixed') :
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy();

		return new SimpleEiField($eiu, $constraints,
				$this, $this, $this, ($this->isReadOnly($eiu) ? null : $this));
	}
	
	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
		return null;
	}

	public function write(Eiu $eiu, $value) {
		$eiu->entry()->writeNativeValue($this, $value);
	}
	
	public function copy(Eiu $eiu, $value, Eiu $copyEiu) {
		return $value;
	}
	
	private function checkMandatory(EiObject $eiObject, $eiFieldValue): bool {
		return $eiFieldValue !== null || $eiObject->isDraft() || !$this->editConfig->isMandatory();
	}
	
	public function testEiFieldValue(Eiu $eiu, $eiFieldValue): bool {
		return $this->checkMandatory($eiu->object()->getEiObject(), $eiFieldValue);
	}
	
	public function validateEiFieldValue(Eiu $eiu, $eiFieldValue, EiFieldValidationResult $validationResult) {
		if (!$this->checkMandatory($eiu->object()->getEiObject(), $eiFieldValue)) {
			$validationResult->addError(Message::createCodeArg('ei_impl_mandatory_err', array('field' => $this->labelLstr), null, 
					Rocket::NS));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyDisplayableEiPropAdapter::getGuiProp()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyDisplayableEiPropAdapter::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		return new GuiFieldProxy($eiu, $this, $this);
	}
	
	/**
	 * @return bool
	 */
	public function isReadOnly(Eiu $eiu): bool {
		if (!WritableEiPropPrivilege::checkForWriteAccess($eiu->entry()->access()->getEiFieldAccess($this))) {
			return true;
		}
		
		if ($eiu->entry()->isDraft() || (!$eiu->entry()->isNew() 
				&& $this->getEditConfig()->isConstant())) {
			return true;
		}
		
		return $this->getEditConfig()->isReadOnly();
	}
	
	public function isMandatory(Eiu $eiu): bool {
		 return $this->getEditConfig()->isMandatory();
	}

	public function createEiPropPrivilege(Eiu $eiu): EiPropPrivilege {
		return new WritableEiPropPrivilege($this->getLabelLstr());
	}
	

// 	public function isWritingAllowed(Attributes $accessAttributes, EiFrame $eiFrame, 
// 			EiObject $eiObject = null) {
// 		return (boolean) $accessAttributes->get('writingAllowed');
// 	}
	
	public function loadMagValue(Eiu $eiu, Mag $option) {
		$option->setValue($eiu->field()->getValue());
	}
	
	public function saveMagValue(Mag $option, Eiu $eiu) {
		$eiu->field()->setValue($option->getValue());
	}
}

class WritableEiPropPrivilege implements EiPropPrivilege {
	const ACCESS_WRITING_ALLOWED_KEY = 'writingAllowed';
	const ACCESS_WRITING_ALLOWED_DEFAULT = true;
	
	private $label;
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	public function getLabel(): string {
		return $this->label;
	}
	
	public function createMagCollection(Attributes $attributes): MagCollection {
		$mc = new MagCollection();
		$mc->addMag(self::ACCESS_WRITING_ALLOWED_KEY, new BoolMag(Rocket::createLstr('ei_impl_field_writable_label', Rocket::NS),
				$attributes->getBool(self::ACCESS_WRITING_ALLOWED_KEY, false, self::ACCESS_WRITING_ALLOWED_DEFAULT)));
		return $mc;
	}
	
	public function buildAttributes(MagCollection $magCollection): Attributes {
		$mag = $magCollection->getMagByPropertyName(self::ACCESS_WRITING_ALLOWED_KEY);
		ArgUtils::assertTrue($mag instanceof BoolMag);
		
		return new Attributes(array(self::ACCESS_WRITING_ALLOWED_KEY => $mag->getValue()));
	}
	
	public static function checkForWriteAccess(EiFieldAccess $eiFieldAccess) {
		if ($eiFieldAccess->isFullyGranted()) {
			return true;
		}
		
		try {
			foreach ($eiFieldAccess->getAttributes() as $attributes) {
				if ($attributes->getBool(self::ACCESS_WRITING_ALLOWED_KEY, false, 
						self::ACCESS_WRITING_ALLOWED_DEFAULT)) {
					return true;
				}
			}
		} catch (AttributesException $e) {
			return self::ACCESS_WRITING_ALLOWED_DEFAULT;
		}
	}
	public function createMag(Attributes $attributes): Mag {
	}

}
