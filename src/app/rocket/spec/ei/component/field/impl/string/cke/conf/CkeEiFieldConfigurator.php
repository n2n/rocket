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
namespace rocket\spec\ei\component\field\impl\string\cke\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
use n2n\util\StringUtils;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\ei\component\field\impl\string\cke\CkeEiField;
use n2n\util\config\LenientAttributeReader;
use n2n\reflection\magic\MagicObjectUnavailableException;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeLinkProvider;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeUtils;

class CkeEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const OPTION_MODE_KEY = 'mode';
	const OPTION_LINK_PROVIDER_LOOKUP_IDS_KEY = 'linkProviders';
	const OPTION_CSS_CONFIG_LOOKUP_ID_KEY = 'cssConfig';
	const OPTION_TABLES_SUPPORTED_KEY = 'tablesSupported';
	const OPTION_BBCODE_KEY = 'bbcode';
	
	private $ckeEiField;
	
	public function __construct(CkeEiField $ckeEiField) {
		parent::__construct($ckeEiField);
		
		$this->ckeEiField = $ckeEiField;
		$this->autoRegister($ckeEiField);
	}

	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection->addMag(new EnumMag(self::OPTION_MODE_KEY, 'Mode',
				array_combine(CkeEiField::getModes(), CkeEiField::getModes()), 
				$lar->getEnum(self::OPTION_MODE_KEY, CkeEiField::getModes(), $this->ckeEiField->getMode())));
		
		$magCollection->addMag(new StringArrayMag(self::OPTION_LINK_PROVIDER_LOOKUP_IDS_KEY, 
				'Link Provider Lookup Ids',
				$lar->getScalarArray(self::OPTION_LINK_PROVIDER_LOOKUP_IDS_KEY)));
		
		$magCollection->addMag(new StringMag(self::OPTION_CSS_CONFIG_LOOKUP_ID_KEY, 
				'Css Config Lookup Id',
				$lar->getString(self::OPTION_CSS_CONFIG_LOOKUP_ID_KEY)));
		
		$magCollection->addMag(new BoolMag(self::OPTION_TABLES_SUPPORTED_KEY, 'Table Editing',
				$lar->getBool(self::OPTION_TABLES_SUPPORTED_KEY, $this->ckeEiField->isTableSupported())));
		
		$magCollection->addMag(new BoolMag(self::OPTION_BBCODE_KEY, 'BBcode',
				$this->attributes->get(self::OPTION_BBCODE_KEY, false, $this->ckeEiField->isBbcodeEnabled())));
				
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::OPTION_MODE_KEY, 
				self::OPTION_LINK_PROVIDER_LOOKUP_IDS_KEY, self::OPTION_CSS_CONFIG_LOOKUP_ID_KEY, 
				self::OPTION_TABLES_SUPPORTED_KEY, self::OPTION_BBCODE_KEY), true), true);
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}
		
		if (StringUtils::endsWith('Html', $propertyAssignation->getEntityProperty()->getName())) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$this->ckeEiField->setMode($this->attributes->getEnum(self::OPTION_MODE_KEY, CkeEiField::getModes(),
				false, $this->ckeEiField->getMode()));
		
		
		$ckeLinkProviderLookupIds = $this->attributes->getScalarArray(self::OPTION_LINK_PROVIDER_LOOKUP_IDS_KEY, false);
		try {
			CkeUtils::lookupCkeLinkProviders($ckeLinkProviderLookupIds, $eiSetupProcess->getN2nContext());
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->ckeEiField->setCkeLinkProviderLookupIds($ckeLinkProviderLookupIds);
		
		
		$ckeCssConfigLookupId = $this->attributes->getString(self::OPTION_CSS_CONFIG_LOOKUP_ID_KEY, false);
		try {
			CkeUtils::lookupCkeCssConfig($ckeCssConfigLookupId, $eiSetupProcess->getN2nContext());
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->ckeEiField->setCkeCssConfigLookupId($ckeCssConfigLookupId);
		
		
		$this->ckeEiField->setTableSupported($this->attributes->getBool(self::OPTION_TABLES_SUPPORTED_KEY, false,
				$this->ckeEiField->isTableSupported()));
		
		$this->ckeEiField->setBbcodeEnabled($this->attributes->getBool(self::OPTION_BBCODE_KEY, false,
				$this->ckeEiField->isBbcodeEnabled()));
	}
	
	private function lookupCssConfig($lookupId, EiSetupProcess $eiSetupProcess) {
		if ($lookupId === null) return null;
		
		$cssConfig = null;
		try {
			$cssConfig = $eiSetupProcess->getN2nContext()->lookup($lookupId);
		} catch (MagicObjectUnavailableException $e) {
			throw $eiSetupProcess->createException('Invalid css config.', $e);
		}
		
		if ($cssConfig instanceof CkeCssConfig) {
			return $cssConfig;
		}
		
		throw $eiSetupProcess->createException('Invalid css config. Reason: ' . get_class($cssConfig) 
				. ' does not implement ' . CkeCssConfig::class);
	}
	
	private function lookupLinkProvider($lookupId, EiSetupProcess $eiSetupProcess) {
		$linkProvider = null;
		try {
			$linkProvider = $eiSetupProcess->getN2nContext()->lookup($lookupId);
		} catch (MagicObjectUnavailableException $e) {
			throw $eiSetupProcess->createException('Invalid link provider defined: ' . $lookupId, $e);
		}
		
		if ($linkProvider instanceof CkeLinkProvider) {
			return $linkProvider;
		}
		
		throw $eiSetupProcess->createException('Invalid link provider defined. Reason: ' . get_class($linkProvider) 
				. ' does not implement ' . CkeLinkProvider::class);
	}
}
