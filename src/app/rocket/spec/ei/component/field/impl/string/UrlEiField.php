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
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\component\EiConfigurator;
use rocket\spec\ei\component\field\impl\string\conf\UrlEiFieldConfigurator;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use n2n\impl\web\dispatch\mag\model\UrlMag;
use n2n\util\uri\Url;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;

class UrlEiField extends AlphanumericEiField {
	private $autoScheme;
	private $allowedSchemes = array();
	private $relativeAllowed = false;
	
	public function getTypeName(): string {
		return "Link";
	}

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new UrlEiFieldConfigurator($this);
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
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$mag = new UrlMag($propertyName, $this->getLabelLstr(), null, $this->isMandatory($entrySourceInfo), 
				$this->getMaxlength());
		if (!empty($this->allowedSchemes)) {
			$mag->setAllowedSchemes($this->allowedSchemes);
		}
		$mag->setRelativeAllowed($this->relativeAllowed);
		$mag->setAutoScheme($this->autoScheme);
		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr()));
		$mag->setAttrs(array('class' => 'rocket-block'));
		return $mag;
	}
	
	public function loadMagValue(FieldSourceInfo $entrySourceInfo, Mag $mag) {
		$value = $entrySourceInfo->getValue();
		if ($value !== null) {
			$value = Url::create($value, true);
		}
		$mag->setValue($value);
	}
	
	public function saveMagValue(Mag $mag, FieldSourceInfo $entrySourceInfo) {
		$value = $mag->getValue();
		if ($value !== null) {
			$value = (string) $value;
		}
		$entrySourceInfo->setValue($value);
	}
}
