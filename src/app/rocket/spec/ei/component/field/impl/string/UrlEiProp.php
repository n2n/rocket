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
namespace rocket\spec\ei\component\field\impl\string;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\EiObject;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\component\field\impl\string\conf\UrlEiPropConfigurator;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\impl\web\dispatch\mag\model\UrlMag;
use n2n\util\uri\Url;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;

class UrlEiProp extends AlphanumericEiProp {
	private $autoScheme;
	private $allowedSchemes = array();
	private $relativeAllowed = false;
	
	public function getTypeName(): string {
		return "Link";
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		return new UrlEiPropConfigurator($this);
	}
	
	public function setAllowedSchemes(array $allowedSchemes) {
		$this->allowedSchemes = $allowedSchemes;
	}
	
	public function getAllowedSchemes(): array {
		return $this->allowedSchemes;
	}
	
	public function isRelativeAllowed(): bool {
		return $this->relativeAllowed;
	}
	
	public function setRelativeAllowed(bool $relativeAllowed) {
		$this->relativeAllowed = $relativeAllowed;
	}
	
	public function setAutoScheme(string $autoScheme = null) {
		$this->autoScheme = $autoScheme;
	}
	
	public function getAutoScheme() {
		return $this->autoScheme;
	}
	
// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInput($propertyPath, array('class' => 'rocket-preview-inpage-component'));
// 	}
	
	public function createMag(string $propertyName, Eiu $eiu): Mag {
		$mag = new UrlMag($propertyName, $this->getLabelLstr(), null, $this->isMandatory($eiu), 
				$this->getMaxlength());
		if (!empty($this->allowedSchemes)) {
			$mag->setAllowedSchemes($this->allowedSchemes);
		}
		$mag->setRelativeAllowed($this->relativeAllowed);
		$mag->setAutoScheme($this->autoScheme);
		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr(), 'class' => 'form-control'));
		$mag->setAttrs(array('class' => 'rocket-block'));
		return $mag;
	}
	
	public function loadMagValue(Eiu $eiu, Mag $mag) {
		$value = $eiu->field()->getValue();
		if ($value !== null) {
			$value = Url::create($value, true);
		}
		$mag->setValue($value);
	}
	
	public function saveMagValue(Mag $mag, Eiu $eiu) {
		$value = $mag->getValue();
		if ($value !== null) {
			$value = (string) $value;
		}
		$eiu->field()->setValue($value);
	}

	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		$value = $eiu->field()->getValue();
		if ($value === null) return null;
		return $view->getHtmlBuilder()->getLink($value, $this->buildLabel($value, $eiu->entryGui()->isViewModeBulky()),
				array('target' => '_blank'));
	}

	public function read(EiObject $eiObject) {
		$urlStr = parent::read($eiObject);
		if ($urlStr === null) return null;

		try {
			return Url::create($urlStr, true);
		} catch (\InvalidArgumentException $e) {
			return null;
		}
	}

	public function write(EiObject $eiObject, $value) {
		if ($value !== null) {
			$value = (string) $value;
		}
		return parent::write($eiObject, $value);
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
