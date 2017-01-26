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
namespace rocket\spec\ei\component\field\impl\string\cke;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\field\impl\string\AlphanumericEiField;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\EiFieldPath;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\util\col\GenericArrayObject;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig;
use rocket\spec\ei\component\field\impl\string\cke\conf\CkeEiFieldConfigurator;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeMag;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeLinkProvider;
use rocket\spec\ei\component\field\impl\string\cke\ui\CkeHtmlBuilder;

class CkeEiField extends AlphanumericEiField {
	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';
	
	private $mode = self::MODE_SIMPLE;
	private $ckeLinkProviderLookupIds;
	private $cssConfigLookupId = null;
	private $tableSupported = false;
	private $bbcodeEnabled = false;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayDefinition->setDefaultDisplayedViewModes(DisplayDefinition::BULKY_VIEW_MODES);
		$this->standardEditDefinition->setMandatory(false);
		
		$this->ckeLinkProviderLookupIds = new GenericArrayObject(null, CkeLinkProvider::class);
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
	    return new CkeEiFieldConfigurator($this);
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	public function setMode($mode) {
		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
	}
	
	public static function getModes() {
		return array(self::MODE_SIMPLE, self::MODE_NORMAL, self::MODE_ADVANCED);
	}
		
	/**
	 * @return \ArrayObject 
	 */
	public function getCkeLinkProviderLookupIds() {
		return $this->ckeLinkProviderLookupIds;
	}
	
	public function setCkeLinkProviderLookupIds(array $ckeLinkProviderLookupIds) {
		ArgUtils::valArray($ckeLinkProviderLookupIds, 'string');
		$this->ckeLinkProviderLookupIds = $ckeLinkProviderLookupIds;
	}
		
	/**
	 * @return CkeCssConfig
	 */
	public function getCkeCssConfigLookupId() {
		return $this->ckeCssConfigLookupId;
	}
	
	public function setCkeCssConfigLookupId(string $ckeCssConfigLookupId = null) {
		$this->ckeCssConfigLookupId = $ckeCssConfigLookupId;
	}
	
	/**
	 * @return bool
	 */
	public function isTableSupported() {
		return $this->tableSupported;
	}
	
	public function setTableSupported(bool $tableSupported) {
		$this->tableSupported = $tableSupported;
	}
		
	/**
	 * @return bool 
	 */
	public function isBbcodeEnabled() {
		return $this->bbcodeEnabled;
	}
	
	public function setBbcodeEnabled(bool $bbcodeEnabled) {
		$this->bbcodeEnabled = $bbcodeEnabled;
	}

	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo) {
	    $value = $entrySourceInfo->getValue(EiFieldPath::from($this));
		$wysiwygHtml = new CkeHtmlBuilder($view);
		if ($this->bbcodeEnabled) {
			return $wysiwygHtml->getWysiwygIframeBbcode($value, $this->obtainCssConfiguration());
		}
		return $wysiwygHtml->getIframe($value, $this->cssConfigLookupId);
	}
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$eiMapping = $entrySourceInfo->getEiMapping();
		return new CkeMag($propertyName, $this->getLabelLstr(), null, $this->isMandatory($entrySourceInfo), 
				null, $this->getMaxlength(), $this->getMode(), $this->isBbcodeEnabled(),
				$this->isTableSupported(), $this->ckeLinkProviderLookupIds, $this->cssConfigLookupId);
	}
	
// 	/**
// 	 * @return \rocket\spec\ei\component\field\WysiwygLinkConfig
// 	 */
// 	private function obtainLinkConfigurations(EiMapping $eiMapping, FieldSourceInfo $entrySourceInfo) {
// 		$n2nContext = $entrySourceInfo->getEiState()->getN2nContext();
		
// 		// @todo @thomas vielleicht im configurator machen und richtige exception werfen
// 		$linkConfigurations = array();
// 		foreach((array) $this->linkConfigClassNames as $linkConfigurationClass) {
// 			try {
// 				if (null !== ($linkConfiguration = $n2nContext->lookup($linkConfigurationClass)) 
// 						&& $linkConfiguration instanceof WysiwygLinkConfig) {
// 					$linkConfiguration->setup($eiMapping, $entrySourceInfo);
// 					$linkConfigurations[] = $linkConfiguration;
// 				}
// 			} catch (MagicObjectUnavailableException $e) {}
// 		}
// 		return $linkConfigurations;
// 	}
	
// 	/**
// 	* @return rocket\spec\ei\component\field\impl\string\wysiwyg\WysiwygCssConfig
// 	*/
// 	private function obtainCssConfiguration() {

// 		// @todo @thomas vielleicht im configurator machen und richtige exception werfen
// 		if (null != ($cssConfigurationClClassNais->cssCoamnfigLoamkuempd) {
// 			try {
// 				if (null !== ($reflectionClass = ReflectionUtils::createReflectionClass($cssConfigurationClass))) {
// 					if ($reflectionClass->implementsInterface(WysiwygCssConfig::class)) {
// 						return ReflectionUtils::createObject($reflectionClass);
// 					}
// 				}
// 			} catch (TypeNotFoundException $e) {}
// 		}
		
// 		return null;
// 	}
}
