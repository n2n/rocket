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
namespace rocket\impl\ei\component\prop\string;

use n2n\util\config\Attributes;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\util\crypt\hash\algorithm\BlowfishAlgorithm;
use rocket\ei\manage\mapping\EiEntry;

use n2n\impl\web\dispatch\mag\model\SecretStringMag;
use n2n\util\crypt\hash\algorithm\Sha256Algorithm;
use n2n\util\crypt\hash\HashUtils;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\string\conf\PasswordEiPropConfigurator;
use rocket\ei\util\model\Eiu;
use n2n\web\dispatch\mag\Mag;

class PasswordEiProp extends AlphanumericEiProp {
	const ALGORITHM_SHA1 = 'sha1';
	const ALGORITHM_MD5 = 'md5';
	const ALGORITHM_BLOWFISH = 'blowfish';
	const ALGORITHM_SHA_256 = 'sha-256';
		
	public function isMandatory(Eiu $eiu): bool {
		return false;
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new PasswordEiPropConfigurator($this);
	}
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		return null;
	}
	
	public function createMag(Eiu $eiu): Mag {
		return new SecretStringMag($this->getLabelCode(), null,
				$eiu->entry()->getEiEntry()->getEiObject()->isNew(), $this->getMaxlength(), 
				array('placeholder' => $this->getLabelCode(), 'class' => 'form-control'));
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			EiEntry $eiEntry, Eiu $eiu) {
		$optionValue = $attributes->get($this->getId());
		$eiObject = $eiEntry->getEiObject();
		if (mb_strlen($optionValue) === 0 && !$eiObject->isNew()) {
			return;
		}
		$propertyValue = null;
		switch ($this->getAttributes()->get(self::OPTION_ALGORITHM_KEY)) {
			case (self::ALGORITHM_BLOWFISH):
				$propertyValue = HashUtils::buildHash($optionValue, new BlowfishAlgorithm());
				break;
			case (self::ALGORITHM_SHA_256):
				$propertyValue = HashUtils::buildHash($optionValue, new Sha256Algorithm());
				break;
			case (self::ALGORITHM_MD5):
				$propertyValue = md5($optionValue);
				break;
			case (self::ALGORITHM_SHA1):
				$propertyValue = sha1($optionValue);
				break;
		}
		$eiEntry->setValue($this->getId(), $propertyValue);
	}
	
	public static function getAlgorithms() {
		return array(self::ALGORITHM_BLOWFISH, self::ALGORITHM_SHA1, self::ALGORITHM_MD5, self::ALGORITHM_SHA_256);
	}
}