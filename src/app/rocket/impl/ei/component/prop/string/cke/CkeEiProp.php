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
namespace rocket\impl\ei\component\prop\string\cke;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\impl\ei\component\prop\string\AlphanumericEiProp;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\EiPropPath;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\util\col\GenericArrayObject;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\conf\CkeEiPropConfigurator;
use rocket\impl\ei\component\prop\string\cke\model\CkeMag;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use rocket\impl\ei\component\prop\string\cke\ui\CkeHtmlBuilder;
use rocket\spec\ei\manage\gui\ViewMode;

class CkeEiProp extends AlphanumericEiProp {
	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';
	
	private $mode = self::MODE_SIMPLE;
	private $ckeLinkProviderLookupIds;
	private $ckeCssConfigLookupId = null;
	private $tableSupported = false;
	private $bbcodeEnabled = false;
	
	public function __construct() {
		$this->getDisplaySettings()->setDefaultDisplayedViewModes(ViewMode::bulky());
		$this->getStandardEditDefinition()->setMandatory(false);
		
		$this->ckeLinkProviderLookupIds = new GenericArrayObject(null, CkeLinkProvider::class);
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
	    return new CkeEiPropConfigurator($this);
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

	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
	    $value = $eiu->field()->getValue(EiPropPath::from($this));

		$ckeCss = null;
		if ($this->ckeCssConfigLookupId !== null) {
			$ckeCss = $view->lookup($this->ckeCssConfigLookupId);
		}

		$linkProviders = array();
		foreach ($this->ckeLinkProviderLookupIds as $linkProviderLookupId) {
			$linkProviders[] = $view->lookup($linkProviderLookupId);
		}
		$ckeHtmlBuidler = new CkeHtmlBuilder($view);
		
		if ($this->bbcodeEnabled) {
			return $ckeHtmlBuidler->getIframe($value, $this->obtainCssConfiguration());
		}

		return $ckeHtmlBuidler->getIframe((string) $value, $ckeCss, $linkProviders);
	}
	
	public function createMag(Eiu $eiu): Mag {
		$eiEntry = $eiu->entry()->getEiEntry();
		return new CkeMag($this->getLabelLstr(), null, $this->isMandatory($eiu),
				null, $this->getMaxlength(), $this->getMode(), $this->isBbcodeEnabled(),
				$this->isTableSupported(), $this->ckeLinkProviderLookupIds, $this->ckeCssConfigLookupId);
	}
	
// 	/**
// 	 * @return \rocket\spec\ei\component\prop\WysiwygLinkConfig
// 	 */
// 	private function obtainLinkConfigurations(EiEntry $eiEntry, Eiu $eiu) {
// 		$n2nContext = $eiu->frame()->getEiFrame()->getN2nContext();
		
// 		// @todo @thomas vielleicht im configurator machen und richtige exception werfen
// 		$linkConfigurations = array();
// 		foreach((array) $this->linkConfigClassNames as $linkConfigurationClass) {
// 			try {
// 				if (null !== ($linkConfiguration = $n2nContext->lookup($linkConfigurationClass)) 
// 						&& $linkConfiguration instanceof WysiwygLinkConfig) {
// 					$linkConfiguration->setup($eiEntry, $eiu);
// 					$linkConfigurations[] = $linkConfiguration;
// 				}
// 			} catch (MagicObjectUnavailableException $e) {}
// 		}
// 		return $linkConfigurations;
// 	}<
	
// 	/**
// 	* @return rocket\impl\ei\component\prop\string\wysiwyg\WysiwygCssConfig
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
