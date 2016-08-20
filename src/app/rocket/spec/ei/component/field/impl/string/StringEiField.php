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

use n2n\l10n\N2nLocale;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use rocket\spec\ei\component\field\impl\string\conf\StringEiFieldConfigurator;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\EiFieldPath;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;

class StringEiField extends AlphanumericEiField {
	private $multiline = false;
	
	public function isMultiline() {
		return $this->multiline;
	}
	
	public function setMultiline($multiline) {
		$this->multiline = (boolean) $multiline;
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new StringEiFieldConfigurator($this);
	}
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo)  {
		$html = $view->getHtmlBuilder();
		
		$value = $entrySourceInfo->getValue(EiFieldPath::from($this));
		
		if ($this->isMultiline()) {
			return $html->getEscBr($value);
		}
		
		return $html->getEsc($value);
	}
	
// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		if ($this->isMultiline()) {
// 			return $view->getFormHtmlBuilder()->getTextarea($propertyPath, array('class' => 'rocket-preview-inpage-component'));
// 		}
// 		return $view->getFormHtmlBuilder()->getInputField($propertyPath, array('class' => 'rocket-preview-inpage-component'));
// 	}
	

	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$mag = new StringMag($propertyName, $this->getLabelLstr(), null,
				$this->isMandatory($entrySourceInfo), 
				$this->getMaxlength(), $this->isMultiline(),
				array('placeholder' => $this->getLabelLstr()->t($entrySourceInfo->getN2nLocale())));
		$mag->setContainerAttrs(array('class' => 'rocket-block'));
		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr()));
		return $mag;
	}
	
	public function isStringRepresentable(): bool {
		return true;
	}

	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		return $this->read($eiObject);
	}

}
