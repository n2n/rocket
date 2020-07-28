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

use n2n\impl\web\dispatch\mag\model\SecretStringMag;
use n2n\util\crypt\hash\HashUtils;
use n2n\util\crypt\hash\algorithm\BlowfishAlgorithm;
use n2n\util\crypt\hash\algorithm\Sha256Algorithm;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\string\conf\PasswordConfig;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;

class PasswordEiProp extends DraftablePropertyEiPropAdapter {
	private $passwordConfig;

	function __construct() {
		parent::__construct();
		
		$this->passwordConfig = new PasswordConfig();
	}
	
	public function prepare() {
		$this->getConfigurator()->addAdaption($this->passwordConfig);
	}
	
	public function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		return null;
	}
	
	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		return new SecretStringMag($this->getLabelLstr(), null,
				$this->isMandatory($eiu), $this->getMaxlength(), 
				array('placeholder' => $this->getLabelLstr()));
	}
	
	
	public function loadSiField(Eiu $eiu, SiField $siField) { }
	
	public function saveSiField(SiField $siField, Eiu $eiu) {
		$value = $option->getValue();
		if (mb_strlen($value) === 0 && !$eiu->entry()->isNew()) {
			return;
		}
		
		$fieldValue = null;
		switch ($this->algorithm) {
			case (self::ALGORITHM_BLOWFISH):
				$fieldValue = HashUtils::buildHash($value, new BlowfishAlgorithm());
				break;
			case (self::ALGORITHM_SHA_256):
				$fieldValue = HashUtils::buildHash($value, new Sha256Algorithm());
				break;
			case (self::ALGORITHM_MD5):
				$fieldValue = md5($value);
				break;
			case (self::ALGORITHM_SHA1):
				$fieldValue = sha1($value);
				break;
			default:
				throw new IllegalStateException('invalid algorithm given: ' . $this->algorithm);
		}
		
		$eiu->field()->setValue($fieldValue);
	}
}