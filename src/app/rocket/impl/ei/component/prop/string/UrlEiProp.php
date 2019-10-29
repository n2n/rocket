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

use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use n2n\impl\web\dispatch\mag\model\UrlMag;
use n2n\util\uri\Url;
use n2n\reflection\property\AccessProxy;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\UrlEntityProperty;
use rocket\si\content\SiField;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\string\conf\UrlConfig;

class UrlEiProp extends AlphanumericEiProp {
	
	private $urlConfig;
	
	function __construct() {
		parent::__construct();
		$this->urlConfig = new UrlConfig();
	}
	
	public function getTypeName(): string {
		return "Link";
	}
	
	public function setObjectPropertyAccessProxy(?AccessProxy $objectPropertyAccessProxy) {
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);
		
		if ($objectPropertyAccessProxy !== null) {
			$objectPropertyAccessProxy->getConstraint()->setWhitelistTypes([Url::class]);
		}
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		if ($entityProperty instanceof UrlEntityProperty) {
			$this->entityProperty = $entityProperty;
			return;
		}
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->entityProperty instanceof UrlEntityProperty) {
			return null;
		}
		
		return parent::buildQuickSearchProp($eiu);
	}

	public function prepare() {
		parent::prepare();
		$this->getConfigurator()->addAdaption($this->urlConfig);
	}

	
// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInput($propertyPath, array('class' => 'rocket-preview-inpage-component'));
// 	}
	
	public function createInSiField(Eiu $eiu): SiField {
		$mag = new UrlMag($this->getLabelLstr(), null, $this->isMandatory($eiu), 
				$this->getMaxlength());
		if (!empty($this->allowedSchemes)) {
			$mag->setAllowedSchemes($this->allowedSchemes);
		}
		$mag->setRelativeAllowed($this->relativeAllowed);
		$mag->setAutoScheme($this->autoScheme);
		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr(), 'class' => 'form-control'));
// 		$mag->setAttrs(array('class' => 'rocket-block'));
		return $mag;
	}
	
	public function loadMagValue(Eiu $eiu, Mag $mag) {
		$value = $eiu->field()->getValue();
		if ($value !== null) {
			$value = Url::create($value, true);
		}
		$mag->setValue($value);
	}
	
	public function saveSiField(SiField $mag, Eiu $eiu) {
		$value = $mag->getValue();
		if ($value !== null) {
			$value = /*(string)*/ $value;
		}
		$eiu->field()->setValue($value);
	}

	public function createOutSiField(Eiu $eiu): SiField  {
		$value = $eiu->field()->getValue();
		if ($value === null) return null;
		return $view->getHtmlBuilder()->getLink($value, $this->buildLabel($value, $eiu->entryGui()->isBulky()),
				array('target' => '_blank'));
	}

	public function read(Eiu $eiu) {
		$urlStr = parent::read($eiu);
		if ($urlStr === null) return null;

		try {
			return Url::create($urlStr, true);
		} catch (\InvalidArgumentException $e) {
			return null;
		}
	}

	public function write(Eiu $eiu, $value) {
		if ($value instanceof Url 
				&& $this->getObjectPropertyAccessProxy()->getConstraint()->getTypeName() != Url::class) {
			$value = (string) $value;
		}
		return parent::write($eiu, $value);
	}

	private function buildLabel(Url $url, bool $isBulkyMode) {
		if ($isBulkyMode) return (string) $url;

		$label = (string) $url->getAuthority();

		$pathParts = $url->getPath()->getPathParts();
		if (!empty($pathParts)) {
			$label .= '/.../' . array_pop($pathParts);
		}

		$query = $url->getQuery();
		if (!$query->isEmpty()) {
			$queryArr = $query->toArray();
			$label .= '?' . key($queryArr) . '=...';
		}

		return $label;
	}
}
