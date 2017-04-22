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
namespace rocket\spec\ei\component\field\impl\string\wysiwyg;

use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\EiFrame;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\field\impl\string\AlphanumericEiProp;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\core\TypeNotFoundException;
use n2n\reflection\ArgUtils;
use n2n\reflection\magic\MagicObjectUnavailableException;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\component\field\impl\string\conf\WysiwygEiPropConfigurator;
use rocket\spec\ei\EiPropPath;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;

class WysiwygEiProp extends AlphanumericEiProp {
	private $mode = WysiwygHtmlBuilder::MODE_SIMPLE;
	private $linkConfigClassNames = array();
	private $cssConfigClassName = null;
	private $tableEditingEnabled = false;
	private $bbcodeEnabled = false;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayDefinition->setDefaultDisplayedViewModes(DisplayDefinition::BULKY_VIEW_MODES);
		$this->standardEditDefinition->setMandatory(false);
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	public function setMode($mode) {
		ArgUtils::valEnum($mode, WysiwygHtmlBuilder::getModes());
		$this->mode = $mode;
	}
		
	public function getLinkConfigClassNames() {
		return $this->linkConfigClassNames;
	}
	
	public function setLinkConfigClassNames(array $linkConfigClassNames) {
		$this->linkConfigClassNames = $linkConfigClassNames;
	}
		
	public function getCssConfigClassName() {
		return $this->cssConfigClassName;
	}
	
	public function setCssConfigClassName($cssConfigClassName) {
		$this->cssConfigClassName = $cssConfigClassName;
	}
		
	public function isTableEditingEnabled() {
		return $this->tableEditingEnabled;
	}
	
	public function setTableEditingEnabled($tableEditingEnabled) {
		$this->tableEditingEnabled = (boolean) $tableEditingEnabled;
	}
		
	public function isBbcodeEnabled() {
		return $this->bbcodeEnabled;
	}
	
	public function setBbcodeEnabled($bbcodeEnabled) {
		$this->bbcodeEnabled = (boolean) $bbcodeEnabled;
	}

	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
	    $value = $eiu->field()->getValue(EiPropPath::from($this));
		$wysiwygHtml = new WysiwygHtmlBuilder($view);
		if ($this->bbcodeEnabled) {
			return $wysiwygHtml->getWysiwygIframeBbcode($value, $this->obtainCssConfiguration());
		}
		return $wysiwygHtml->getWysiwygIframeHtml($value, $this->obtainCssConfiguration());
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
	    return new WysiwygEiPropConfigurator($this);
	}
	
	public function createPreviewUiComponent(EiFrame $eiFrame = null, HtmlView $view, $value) {
		return new Raw($value);
	}

// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		$wysiwygHtml = new WysiwygHtmlBuilder($view);
// 		return $wysiwygHtml->getWysiwygEditor($propertyPath, $this->mode,
// 				$this->getAttributes()->get('bbcode'), true, $this->getAttributes()->get('tableEditing'), $this->obtainLinkConfigurations(), 
// 				$this->obtainCssConfiguration(), array('class' => 'rocket-preview-inpage-component'));
// 	}
	
	public function createMag(string $propertyName, Eiu $eiu): Mag {
		$eiMapping = $eiu->entry()->getEiMapping();
		return new WysiwygOption($propertyName, $this->getLabelLstr(), null,
				$this->isMandatory($eiu), 
				null, $this->getMaxlength(), $this->getMode(), $this->isBbcodeEnabled(),
				$this->isTableEditingEnabled(), $this->obtainLinkConfigurations($eiMapping, $eiu), 
				$this->obtainCssConfiguration());
	}
	
	/**
	 * @return \rocket\spec\ei\component\field\WysiwygLinkConfig
	 */
	private function obtainLinkConfigurations(EiMapping $eiMapping, Eiu $eiu) {
		$n2nContext = $eiu->frame()->getEiFrame()->getN2nContext();
		
		// @todo @thomas vielleicht im configurator machen und richtige exception werfen
		$linkConfigurations = array();
		foreach((array) $this->linkConfigClassNames as $linkConfigurationClass) {
			try {
				if (null !== ($linkConfiguration = $n2nContext->lookup($linkConfigurationClass)) 
						&& $linkConfiguration instanceof WysiwygLinkConfig) {
					$linkConfiguration->setup($eiMapping, $eiu);
					$linkConfigurations[] = $linkConfiguration;
				}
			} catch (MagicObjectUnavailableException $e) {}
		}
		return $linkConfigurations;
	}
	
	/**
	* @return rocket\spec\ei\component\field\impl\string\wysiwyg\WysiwygCssConfig
	*/
	private function obtainCssConfiguration() {

		// @todo @thomas vielleicht im configurator machen und richtige exception werfen
		if (null != ($cssConfigurationClass = $this->cssConfigClassName)) {
			try {
				if (null !== ($reflectionClass = ReflectionUtils::createReflectionClass($cssConfigurationClass))) {
					if ($reflectionClass->implementsInterface(WysiwygCssConfig::class)) {
						return ReflectionUtils::createObject($reflectionClass);
					}
				}
			} catch (TypeNotFoundException $e) {}
		}
		
		return null;
	}
}
