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

use rocket\impl\ei\component\prop\string\AlphanumericEiProp;
use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\StringUtils;
use n2n\core\N2N;
use rocket\si\content\SiField;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\si\content\impl\StringInSiField;
use rocket\impl\ei\component\prop\string\cke\conf\CkeConfig;
use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;

class CkeEiProp extends AlphanumericEiProp {
	/**
	 * @var CkeConfig
	 */
	private $ckeConfig;
	
	public function __construct() {
		parent::__construct();
		
		$this->getDisplayConfig()->setDefaultDisplayedViewModes(ViewMode::bulky());
		$this->getEditConfig()->setMandatory(false);
		
		$this->ckeConfig = new CkeConfig();
	}
	
	public function prepare() {
		$this->getConfigurator()->addAdaption($this->ckeConfig);
	}
	
	public function createOutSiField(Eiu $eiu): SiField {
	    $value = $eiu->field()->getValue(EiPropPath::from($this));
	    if ($value === null) {
	    	return SiFields::stringOut('');
	    }
	    
		if ($eiu->guiFrame()->isCompact()) {
			return SiFields::stringOut(StringUtils::reduce(html_entity_decode(strip_tags($value), null, N2N::CHARSET), 50, '...'));
		}

// 		$ckeHtmlBuidler = new CkeHtmlBuilder($view);

// 		return $ckeHtmlBuidler->getIframe((string) $value, $this->ckeCssConfig, (array) $this->ckeLinkProviders);

		return SiFields::stringOut((string) $value);
	}
	
	public function createInSiField(Eiu $eiu): SiField {
		$ckeInField = SiFields::ckeIn($eiu->field()->getValue())
				->setMinlength($this->getAlphanumericConfig()->getMinlength())
				->setMaxlength($this->getAlphanumericConfig()->getMaxlength())
				->setMandatory($this->getEditConfig()->isMandatory())
				->setMode($this->ckeConfig->getMode());
		
		if (null !== ($ckeCssConfig = $this->ckeConfig->getCkeCssConfig())) {
			$contentCssUrls = $ckeCssConfig->getContentCssUrls($eiu);
			ArgUtils::valArrayReturn($contentCssUrls, $ckeCssConfig, 'getContentCssUrls', Url::class);
			
			$ckeInField->setContentCssUrls($contentCssUrls)
					->setBodyId($ckeCssConfig->getBodyId())
					->setBodyClass($ckeCssConfig->getBodyClass())
					->setBodyClass($ckeCssConfig->getAdditionalStyles());
		}
		
		return $ckeInField;
		
// 		$eiu->entry()->getEiEntry();
		
// 		return new CkeMag($this->getLabelLstr(), null, $this->isMandatory($eiu),
// 				null, $this->getMaxlength(), $this->getMode(), $this->bbcode,
// 				$this->isTableSupported(), (array) $this->getCkeLinkProviders(), $this->getCkeCssConfig());
	}
	
	function saveSiField(SiField $siField, Eiu $eiu) {
		CastUtils::assertTrue($siField instanceof StringInSiField);
		$eiu->field()->setValue($siField->getValue());
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