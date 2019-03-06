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
namespace rocket\impl\ei\component\prop\string\cke\conf;

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\component\EiSetup;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\util\StringUtils;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\impl\ei\component\prop\string\cke\CkeEiProp;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\util\magic\MagicObjectUnavailableException;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use rocket\impl\ei\component\prop\string\cke\model\CkeUtils;
use n2n\persistence\meta\structure\Column;
use rocket\impl\ei\component\prop\string\cke\model\CkeState;
use n2n\util\type\CastUtils;

class CkeEiPropConfigurator extends AdaptableEiPropConfigurator {
	const PROP_MODE_KEY = 'mode';
	const PROP_LINK_PROVIDER_LOOKUP_IDS_KEY = 'linkProviders';
	const PROP_CSS_CONFIG_LOOKUP_ID_KEY = 'cssConfig';
	const PROP_TABLES_SUPPORTED_KEY = 'tablesSupported';
	const PROP_BBCODE_KEY = 'bbcode';
	
	private $ckeEiProp;
	
	public function __construct(CkeEiProp $ckeEiProp) {
		parent::__construct($ckeEiProp);
		
		$this->ckeEiProp = $ckeEiProp;
		$this->autoRegister($ckeEiProp);
	}

	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		$this->attributes->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);	
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		$ckeState = $n2nContext->lookup(CkeState::class);
		CastUtils::assertTrue($ckeState instanceof CkeState);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection->addMag(self::PROP_MODE_KEY, new EnumMag('Mode',
				array_combine(CkeEiProp::getModes(), CkeEiProp::getModes()), 
				$lar->getEnum(self::PROP_MODE_KEY, CkeEiProp::getModes(), $this->ckeEiProp->getMode())));
		
		$magCollection->addMag(self::PROP_LINK_PROVIDER_LOOKUP_IDS_KEY, 
				new StringArrayMag('Link Provider Lookup Ids', $lar->getScalarArray(self::PROP_LINK_PROVIDER_LOOKUP_IDS_KEY), false,
						['class' => 'hangar-autocompletion', 'data-suggestions' => StringUtils::jsonEncode($ckeState->getRegisteredCkeLinkProviderLookupIds())]));
		
		$magCollection->addMag(self::PROP_CSS_CONFIG_LOOKUP_ID_KEY, new StringMag('Css Config Lookup Id',
				$lar->getString(self::PROP_CSS_CONFIG_LOOKUP_ID_KEY), false, null, false, null, 
				['class' => 'hangar-autocompletion', 'data-suggestions' => StringUtils::jsonEncode($ckeState->getRegisteredCkeCssConfigLookupIds())]));
		
		$magCollection->addMag(self::PROP_TABLES_SUPPORTED_KEY, new BoolMag('Table Editing',
				$lar->getBool(self::PROP_TABLES_SUPPORTED_KEY, $this->ckeEiProp->isTableSupported())));
		$magCollection->addMag(self::PROP_BBCODE_KEY, new BoolMag('BBcode',
				$lar->getBool(self::PROP_BBCODE_KEY, $this->ckeEiProp->isBbcode())));
				
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::PROP_MODE_KEY, 
				self::PROP_LINK_PROVIDER_LOOKUP_IDS_KEY, self::PROP_CSS_CONFIG_LOOKUP_ID_KEY, 
				self::PROP_TABLES_SUPPORTED_KEY, self::PROP_BBCODE_KEY), true), true);
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}
		
		if (StringUtils::endsWith('Html', $propertyAssignation->getObjectPropertyAccessProxy(true)->getPropertyName())) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$this->ckeEiProp->setMode($this->attributes->optEnum(self::PROP_MODE_KEY, CkeEiProp::getModes(), $this->ckeEiProp->getMode(), false));
		
		$ckeState = $eiSetupProcess->getN2nContext()->lookup(CkeState::class);
		CastUtils::assertTrue($ckeState instanceof CkeState);
		
		$ckeLinkProviderLookupIds = $this->attributes->getScalarArray(self::PROP_LINK_PROVIDER_LOOKUP_IDS_KEY, false);
		try {
			$ckeLinkProviders = CkeUtils::lookupCkeLinkProviders($ckeLinkProviderLookupIds, $eiSetupProcess->getN2nContext());
			foreach ($ckeLinkProviders as $ckeLinkProvider) {
				$ckeState->registerCkeLinkProvider($ckeLinkProvider);
			}
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->ckeEiProp->setCkeLinkProviders($ckeLinkProviders);
		
		$ckeCssConfigLookupId = $this->attributes->getString(self::PROP_CSS_CONFIG_LOOKUP_ID_KEY, false, null, true);
		try {
			$ckeCssConfig = CkeUtils::lookupCkeCssConfig($ckeCssConfigLookupId, $eiSetupProcess->getN2nContext());
			if (null !== $ckeCssConfig) {
				$ckeState->registerCkeCssConfig($ckeCssConfig);
			}
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->ckeEiProp->setCkeCssConfig($ckeCssConfig);
		
		$this->ckeEiProp->setTableSupported($this->attributes->getBool(self::PROP_TABLES_SUPPORTED_KEY, false,
				$this->ckeEiProp->isTableSupported()));
		
		$this->ckeEiProp->setBbcode($this->attributes->getBool(self::PROP_BBCODE_KEY, false,
				$this->ckeEiProp->isBbcode()));
	}
	
	private function lookupCssConfig($lookupId, EiSetup $eiSetupProcess) {
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
	
	private function lookupLinkProvider($lookupId, EiSetup $eiSetupProcess) {
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
