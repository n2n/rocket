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
namespace rocket\spec\ei\component\field\impl\date\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use n2n\l10n\DateTimeFormat;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\date\DateTimeEiField;
use n2n\reflection\CastUtils;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\config\LenientAttributeReader;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
 
class DateTimeEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const MAG_DATE_STYLE_KEY = 'dateStyle';
	const MAG_TIME_STYLE_KEY = 'timeStyle';
	
	private $dateTimeEiField;
	
	public function __construct(DateTimeEiField $dateTimeEiField) {
		parent::__construct($dateTimeEiField);
		$this->autoRegister();
		$this->setMaxCompatibilityLevel(CompatibilityLevel::SUITABLE);
		
		$this->dateTimeEiField = $dateTimeEiField;
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$styles = DateTimeFormat::getStyles();
		$styleOptions = array_combine($styles, $styles);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection->addMag(new EnumMag(self::MAG_DATE_STYLE_KEY, 'Date Style', $styleOptions, 
				$lar->getEnum(self::MAG_DATE_STYLE_KEY, $styles, $this->dateTimeEiField->getDateStyle()), true));
		$magCollection->addMag(new EnumMag(self::MAG_TIME_STYLE_KEY, 'Time Style', $styleOptions, 
				$lar->getEnum(self::MAG_TIME_STYLE_KEY, $styles, $this->dateTimeEiField->getTimeStyle()), true));
		return new MagForm($magCollection);
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		CastUtils::assertTrue($this->eiComponent instanceof DateTimeEiField);
		
		if ($this->attributes->contains(self::MAG_DATE_STYLE_KEY)) {
			try {
				$this->eiComponent->setDateStyle($this->attributes->get(self::MAG_DATE_STYLE_KEY));
			} catch (\InvalidArgumentException $e) {
				throw $eiSetupProcess->createException('Invalid date style', $e);
			}
		}
		
		if ($this->attributes->contains(self::MAG_TIME_STYLE_KEY)) {
			try {
				$this->eiComponent->setTimeStyle($this->attributes->get(self::MAG_TIME_STYLE_KEY));
			} catch (\InvalidArgumentException $e) {
				throw $eiSetupProcess->createException('Invalid time style', $e);
			}
		}
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()->readValues(
				array(self::MAG_DATE_STYLE_KEY, self::MAG_TIME_STYLE_KEY), true), true);
	}
}
