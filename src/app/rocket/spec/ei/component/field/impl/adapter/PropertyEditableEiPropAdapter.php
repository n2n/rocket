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
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\util\config\Attributes;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\mapping\impl\Writable;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;

use rocket\spec\ei\manage\mapping\EiField;
use rocket\spec\ei\manage\mapping\impl\SimpleEiField;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\PrivilegedEiProp;
use rocket\spec\security\EiPropPrivilege;
use n2n\reflection\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use rocket\spec\ei\security\EiPropAccess;
use n2n\util\config\AttributesException;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\l10n\MessageCode;
use rocket\spec\ei\manage\mapping\impl\Validatable;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\mapping\impl\Copyable;
use rocket\spec\ei\manage\gui\GuiProp;
use rocket\spec\ei\manage\gui\GuiPropFork;

abstract class PropertyEditableEiPropAdapter extends PropertyDisplayableEiPropAdapter implements StatelessEditable, Writable, 
		PrivilegedEiProp, Validatable, Copyable {
	protected $standardEditDefinition;
	
	public function __construct() {
		parent::__construct();
		
		$this->standardEditDefinition = new StandardEditDefinition();
	}
	
	/**
	 * @return \rocket\spec\ei\component\field\impl\adapter\StandardEditDefinition
	 */
	public function getStandardEditDefinition() {
		return $this->standardEditDefinition;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerStandardEditDefinition($this->standardEditDefinition);
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

		return new SimpleEiField($eiu->entry()->getEiObject(), 
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy(), 
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
	
	public function getGuiProp() {
		return $this;
	}
	
	public function getGuiPropFork() {
		return null;
	}
	
	public function buildGuiField(Eiu $eiu) {
		return new StatelessEditElement($this, $eiu);
	}
	
	/**
	 * @return bool
	 */
	public function isReadOnly(Eiu $eiu): bool {
		if (!WritableEiPropPrivilege::checkForWriteAccess($eiu->frame()->getEiFrame()->getEiExecution()
				->createEiPropAccess(EiPropPath::from($this)))) {
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

	public function createEiPropPrivilege(N2nContext $n2nContext): EiPropPrivilege {
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
	
	public function createMag(string $propertyName, Attributes $attributes): Mag {
		return new BoolMag(new Lstr('ei_impl_field_writable_label', Rocket::NS),
				$attributes->getBool(self::ACCESS_WRITING_ALLOWED_KEY, false, self::ACCESS_WRITING_ALLOWED_DEFAULT));
	}
	
	public function buildAttributes(Mag $mag): Attributes {
		ArgUtils::assertTrue($mag instanceof BoolMag);
		
		return new Attributes(array(self::ACCESS_WRITING_ALLOWED_KEY => $mag->getValue()));
	}
	
	public static function checkForWriteAccess(EiPropAccess $eiAccess) {
		if ($eiAccess->isFullyGranted()) {
			return true;
		}
		
		try {
			foreach ($eiAccess->getAttributes() as $attributes) {
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
