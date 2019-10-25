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

use n2n\l10n\N2nLocale;
use rocket\ei\util\Eiu;
use n2n\util\StringUtils;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

class StringEiProp extends AlphanumericEiProp {
	
	private $stringConfig;
	
	function __construct() {
		parent::__construct();
		
		$this->stringConfig = new StringConfig();
	}
	
	function adaptConfigurator(AdaptableEiPropConfigurator $configurator) {
		parent::adaptConfigurator($configurator);
		$configurator->addAdaption($this->stringConfig);
	}
	
	public function createOutSiField(Eiu $eiu): SiField  {
		return SiFields::stringOut($eiu->field()->getValue())
				->setMultiline($this->isMultiline());
	}

	public function createInSiField(Eiu $eiu): SiField {
		$addonConfig = $this->getAddonConfig();
		
		return SiFields::stringIn($eiu->field()->getValue())
				->setMandatory($this->isMandatory($eiu))
				->setMinlength($this->getMinlength())
				->setMaxlength($this->getMaxlength())
				->setMultiline($this->isMultiline())
				->setPrefixAddons($addonConfig->getPrefixSiCrumbGroups())
				->setSuffixAddons($addonConfig->getSuffixSiCrumbGroups());
	}
	
	public function isStringRepresentable(): bool {
		return true;
	}

	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		return StringUtils::strOf($eiu->object()->readNativValue($this), true);
	}
}
