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
namespace rocket\spec\ei\component\field\impl\string;

use n2n\impl\web\ui\view\html\HtmlView;

use n2n\impl\web\dispatch\mag\model\SecretStringMag;
use rocket\spec\ei\component\field\impl\string\conf\PasswordEiFieldConfigurator;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\web\dispatch\mag\Mag;
use n2n\util\crypt\hash\algorithm\BlowfishAlgorithm;
use n2n\util\crypt\hash\algorithm\Sha256Algorithm;
use n2n\util\crypt\hash\HashUtils;

class PasswordEiField extends AlphanumericEiField {
	const ALGORITHM_SHA1 = 'sha1';
	const ALGORITHM_MD5 = 'md5';
	const ALGORITHM_BLOWFISH = 'blowfish';
	const ALGORITHM_SHA_256 = 'sha-256';
	
	private $algorithm = self::ALGORITHM_BLOWFISH;
	
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	public function setAlgorithm($algorithm) {
		$this->algorithm = $algorithm;
	}
	
	public function isMandatory(Eiu $eiu): bool {
		return false;
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new PasswordEiFieldConfigurator($this);
	}
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		return null;
	}
	
	public function createMag(string $propertyName, Eiu $eiu): Mag {
		return new SecretStringMag($propertyName, $this->getLabelLstr(), null,
				$eiu->entry()->getEiMapping()->getEiSelection()->isNew(), $this->getMaxlength(), 
				array('placeholder' => $this->getLabelLstr()));
	}
	
	public function saveMagValue(Mag $option, Eiu $eiu) {
		$rawPassword = $option->getValue();
		if ($rawPassword === null) return;
		
		$value = null;
		switch ($this->getAlgorithm()) {
			case (PasswordEiField::ALGORITHM_BLOWFISH):
				$value = HashUtils::buildHash($rawPassword, new BlowfishAlgorithm());
				break;
			case (PasswordEiField::ALGORITHM_SHA_256):
				$value = HashUtils::buildHash($rawPassword, new Sha256Algorithm());
				break;
			case (PasswordEiField::ALGORITHM_MD5):
				$value = md5($rawPassword);
				break;
			case (PasswordEiField::ALGORITHM_SHA1):
				$value = sha1($rawPassword);
				break;
		}
		
		$eiu->field()->setValue($value);
	}
	
	public static function getAlgorithms() {
		return array(self::ALGORITHM_BLOWFISH, self::ALGORITHM_SHA1, self::ALGORITHM_MD5, self::ALGORITHM_SHA_256);
	}
}