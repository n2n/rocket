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

use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\mapping\impl\SimpleMappable;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\PrivilegedEiField;
use rocket\spec\security\EiFieldPrivilege;
use n2n\reflection\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use rocket\spec\ei\security\EiFieldAccess;
use n2n\util\config\AttributesException;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\l10n\MessageCode;
use rocket\spec\ei\manage\mapping\impl\Validatable;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\EiFieldPath;

abstract class EditableEiFieldAdapter extends DisplayableEiFieldAdapter implements StatelessEditable, Writable, 
		PrivilegedEiField, Validatable {
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

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		$eiFieldConfigurator = parent::createEiFieldConfigurator();
		IllegalStateException::assertTrue($eiFieldConfigurator instanceof AdaptableEiFieldConfigurator);
		$eiFieldConfigurator->registerStandardEditDefinition($this->standardEditDefinition);
		return $eiFieldConfigurator;
	}
		
// 	protected function checkForWriteAccess(EiMapping $eiMapping) {
// 		return true;
// // 		$ssPrivilegeConstraint = $eiMapping->getSelectionPrivilegeConstraint();
// // 		if ($ssPrivilegeConstraint === null) return true;
		
// // 		foreach ($ssPrivilegeConstraint->getAccessGrants() as $accessGrant) {
// // 			if (!$accessGrant->isRestricted() || $accessGrant->getAttributesById($this->getId())
// // 					->get(self::ACCESS_WRITING_ALLOWED_KEY, self::ACCESS_WRITING_ALLOWED_DEFAULT)) return true;			
// // 		}
		
// // 		return false;
// 	}
	
	public function buildMappable(Eiu $eiu) {
		if ($eiu->entry()->isDraft()) {
			return parent::buildMappable($eiu);
		}

		return new SimpleMappable($eiu->entry()->getEiSelection(), 
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy(), 
				$this, $this, $this);
	}
	
	public function buildMappableFork(EiObject $eiObject, Mappable $mappable = null) {
		return null;
	}

	public function write(EiObject $eiObject, $value) {
		$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $value);
	}
	
	private function checkMandatory(EiObject $eiObject, $mappableValue): bool {
		return $mappableValue !== null || $eiObject->isDraft() || !$this->standardEditDefinition->isMandatory();
	}
	
	public function testMappableValue(EiObject $eiObject, $mappableValue): bool {
		return $this->checkMandatory($eiObject, $mappableValue);
	}
	
	public function validateMappableValue(EiObject $eiObject, $mappableValue, FieldErrorInfo $fieldErrorInfo) {
		if (!$this->checkMandatory($eiObject, $mappableValue)) {
			$fieldErrorInfo->addError(new MessageCode('ei_impl_mandatory_err', array('field' => $this->labelLstr), null, 
					Rocket::NS));
		}
	}
	
	public function getGuiField() {
		return $this;
	}
	
	public function getGuiFieldFork() {
		return null;
	}
	
	public function buildGuiElement(Eiu $eiu) {
		return new StatelessEditElement($this, $eiu);
	}
	
	/**
	 * @return bool
	 */
	public function isReadOnly(Eiu $eiu): bool {
		if (!WritableEiFieldPrivilege::checkForWriteAccess($eiu->frame()->getEiState()->getEiExecution()
				->createEiFieldAccess(EiFieldPath::from($this)))) {
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

	public function createEiFieldPrivilege(N2nContext $n2nContext): EiFieldPrivilege {
		return new WritableEiFieldPrivilege();
	}
	

// 	public function isWritingAllowed(Attributes $accessAttributes, EiState $eiState, 
// 			EiSelection $eiSelection = null) {
// 		return (boolean) $accessAttributes->get('writingAllowed');
// 	}
	
	public function loadMagValue(Eiu $eiu, Mag $option) {
		$option->setValue($eiu->field()->getValue());
	}
	
	public function saveMagValue(Mag $option, Eiu $eiu) {
		$eiu->field()->setValue($option->getValue());
	}
}

class WritableEiFieldPrivilege implements EiFieldPrivilege {
	const ACCESS_WRITING_ALLOWED_KEY = 'writingAllowed';
	const ACCESS_WRITING_ALLOWED_DEFAULT = true;
	
	public function createMag(string $propertyName, Attributes $attributes): Mag {
		return new BoolMag($propertyName, new Lstr('ei_impl_field_writable_label', Rocket::NS),
				$attributes->getBool(self::ACCESS_WRITING_ALLOWED_KEY, false, self::ACCESS_WRITING_ALLOWED_DEFAULT));
	}
	
	public function buildAttributes(Mag $mag): Attributes {
		ArgUtils::assertTrue($mag instanceof BoolMag);
		
		return new Attributes(array(self::ACCESS_WRITING_ALLOWED_KEY => $mag->getValue()));
	}
	
	public static function checkForWriteAccess(EiFieldAccess $eiAccess) {
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
