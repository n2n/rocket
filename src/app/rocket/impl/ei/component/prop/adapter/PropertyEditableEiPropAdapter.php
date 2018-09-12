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

use n2n\reflection\property\TypeConstraint;
use n2n\util\config\Attributes;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\manage\entry\impl\Writable;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\entry\impl\SimpleEiField;
use rocket\ei\manage\EiObject;
use rocket\ei\component\prop\PrivilegedEiProp;
use rocket\ei\manage\security\privilege\EiPropPrivilege;
use n2n\reflection\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use rocket\ei\manage\security\EiFieldAccess;
use n2n\util\config\AttributesException;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\FieldErrorInfo;
use n2n\l10n\MessageCode;
use rocket\ei\manage\entry\impl\Validatable;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\impl\Copyable;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\manage\gui\GuiProp;

abstract class PropertyEditableEiPropAdapter extends PropertyDisplayableEiPropAdapter implements StatelessEditable, Writable, 
		PrivilegedEiProp, Validatable, Copyable {
	protected $standardEditDefinition;

	/**
	 * @return \rocket\impl\ei\component\prop\adapter\StandardEditDefinition
	 */
	public function getStandardEditDefinition() {
		if ($this->standardEditDefinition === null) {
			$this->standardEditDefinition = new StandardEditDefinition();
		}

		return $this->standardEditDefinition;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerStandardEditDefinition($this->getStandardEditDefinition());
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
	
	public function buildEiField(Eiu $eiu) {
		if ($eiu->entry()->isDraft()) {
			return parent::buildEiField($eiu);
		}

		$objectPropertyAccessProxy = $this->getObjectPropertyAccessProxy();
		$constraints = $objectPropertyAccessProxy === null ? TypeConstraint::createSimple('mixed') :
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy();

		return new SimpleEiField($eiu->entry()->getEiObject(), $constraints,
				$this, $this, $this, ($this->isReadOnly($eiu) ? null : $this));
	}
	
	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
		return null;
	}

	public function write(EiObject $eiObject, $value) {
		$this->getObjectPropertyAccessProxy()->setValue($eiObject->getEiEntityObj()->getEntityObj(), $value);
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		return $value;
	}
	
	private function checkMandatory(EiObject $eiObject, $eiFieldValue): bool {
		return $eiFieldValue !== null || $eiObject->isDraft() || !$this->standardEditDefinition->isMandatory();
	}
	
	public function testEiFieldValue(EiObject $eiObject, $eiFieldValue): bool {
		return $this->checkMandatory($eiObject, $eiFieldValue);
	}
	
	public function validateEiFieldValue(EiObject $eiObject, $eiFieldValue, FieldErrorInfo $fieldErrorInfo) {
		if (!$this->checkMandatory($eiObject, $eiFieldValue)) {
			$fieldErrorInfo->addError(new MessageCode('ei_impl_mandatory_err', array('field' => $this->labelLstr), null, 
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
		return new StatelessEditElement($this, $eiu);
	}
	
	/**
	 * @return bool
	 */
	public function isReadOnly(Eiu $eiu): bool {
		if (!WritableEiPropPrivilege::checkForWriteAccess($eiu->entry()->access()->getEiFieldAccess($this))) {
			return true;
		}
		
		if ($eiu->entry()->isDraft() || (!$eiu->entry()->isNew() 
				&& $this->standardEditDefinition->isConstant())) {
			return true;
		}
		
		return $this->standardEditDefinition->isReadOnly();
	}
	
	public function isMandatory(Eiu $eiu): bool {
		 return $this->standardEditDefinition->isMandatory();
	}

	public function createEiPropPrivilege(Eiu $eiu): EiPropPrivilege {
		return new WritableEiPropPrivilege();
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
	
	public function createMag(Attributes $attributes): Mag {
		return new BoolMag(new Lstr('ei_impl_field_writable_label', Rocket::NS),
				$attributes->getBool(self::ACCESS_WRITING_ALLOWED_KEY, false, self::ACCESS_WRITING_ALLOWED_DEFAULT));
	}
	
	public function buildAttributes(Mag $mag): Attributes {
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
}
