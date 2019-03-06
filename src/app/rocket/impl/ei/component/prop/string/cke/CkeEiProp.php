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
use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\util\col\GenericArrayObject;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\conf\CkeEiPropConfigurator;
use rocket\impl\ei\component\prop\string\cke\model\CkeMag;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use rocket\impl\ei\component\prop\string\cke\ui\CkeHtmlBuilder;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\StringUtils;
use n2n\core\N2N;

class CkeEiProp extends AlphanumericEiProp {
	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';
	
	private $mode = self::MODE_SIMPLE;
	private $ckeLinkProviders;
	private $ckeCssConfig = null;
	private $tableSupported = false;
	private $bbcode = false;
	
	public function __construct() {
		$this->getDisplayConfig()->setDefaultDisplayedViewModes(ViewMode::bulky());
		$this->getEditConfig()->setMandatory(false);
		
		$this->ckeLinkProviders = new GenericArrayObject(null, CkeLinkProvider::class);
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
	public function getCkeLinkProviders() {
		return $this->ckeLinkProviders;
	}
	
	public function setCkeLinkProviders(array $ckeLinkProviders) {
		$this->ckeLinkProviders->exchangeArray($ckeLinkProviders);
	}
		
	/**
	 * @return CkeCssConfig
	 */
	public function getCkeCssConfig() {
		return $this->ckeCssConfig;
	}
	
	public function setCkeCssConfig(CkeCssConfig $ckeCssConfig = null) {
		$this->ckeCssConfig = $ckeCssConfig;
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
	
	public function isBbcode() {
		return $this->bbcode;
	}
	
	public function setBbcode(bool $bbcode) {
		$this->bbcode = $bbcode;
	}

	public function createUiComponent(HtmlView $view, Eiu $eiu) {
	    $value = $eiu->field()->getValue(EiPropPath::from($this));
	    if ($value === null) return null;
	    
		if ($eiu->gui()->isCompact()) {
			return StringUtils::reduce(html_entity_decode(strip_tags($value), null, N2N::CHARSET), 50, '...');
		}

		$ckeHtmlBuidler = new CkeHtmlBuilder($view);

		return $ckeHtmlBuidler->getIframe((string) $value, $this->ckeCssConfig, (array) $this->ckeLinkProviders);
	}
	
	public function createMag(Eiu $eiu): Mag {
		$eiEntry = $eiu->entry()->getEiEntry();
		
		return new CkeMag($this->getLabelLstr(), null, $this->isMandatory($eiu),
				null, $this->getMaxlength(), $this->getMode(), $this->bbcode,
				$this->isTableSupported(), (array) $this->getCkeLinkProviders(), $this->getCkeCssConfig());
	}
	
// 	/**
// 	 * @return \rocket\ei\component\prop\WysiwygLinkConfig
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