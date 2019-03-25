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

use n2n\l10n\N2nLocale;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;

use rocket\impl\ei\component\prop\string\conf\StringEiPropConfigurator;
use rocket\ei\EiPropPath;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\util\StringUtils;
use n2n\impl\web\ui\view\html\HtmlElement;

class StringEiProp extends AlphanumericEiProp {
	private $multiline = false;
	
	public function isMultiline() {
		return $this->multiline;
	}
	
	public function setMultiline($multiline) {
		$this->multiline = (boolean) $multiline;
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new StringEiPropConfigurator($this);
	}
	
	public function createUiComponent(HtmlView $view, Eiu $eiu)  {
		$html = $view->getHtmlBuilder();
		
		$value = $eiu->field()->getValue(EiPropPath::from($this));
		
// 		if ($eiu->gui()->isCompact()) {
// 			return new HtmlElement('div', ['class' => 'text-truncate'], $value);
// 		}
		
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

	public function createMag(Eiu $eiu): Mag {
		$mag = new StringMag($this->getLabelLstr(), null, $this->isMandatory($eiu), 
				$this->getMaxlength(), $this->isMultiline(),
				array('placeholder' => $this->getLabelLstr()->t($eiu->frame()->getN2nLocale())));
// 		$mag->setAttrs(array('class' => 'rocket-block'));
		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr()));
// 		$mag->setHelpTextLstr($this->getHelpTextLstr());
		return $mag;
	}
	
	public function isStringRepresentable(): bool {
		return true;
	}

	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		return StringUtils::strOf($eiu->object()->readNativValue($this), true);
	}

}
