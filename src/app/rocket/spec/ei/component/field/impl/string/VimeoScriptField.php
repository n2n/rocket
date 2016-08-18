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

use n2n\ui\view\impl\html\HtmlView;
use n2n\ui\Raw;
use n2n\dispatch\mag\impl\model\StringMag;
use n2n\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\EntrySourceInfo;

die('export vimeo to media module');

class VimeoEiField extends AlphanumericEiField {
	
	public function getTypeName(): string {
		return 'Vimeo Video';
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\impl\string\AlphanumericEiField::createOutputUiComponent()
	 */
	public function createOutputUiComponent(
			HtmlView $view, EntrySourceInfo $entrySourceInfo)  {
		$html = $view->getHtmlBuilder();
		$eiSelection = $eiMapping->getEiSelection();
		$value = $this->getPropertyAccessProxy()->getValue($eiSelection->getCurrentEntity());
		if ($value === null) return null;
		
		$raw = '<iframe src="//player.vimeo.com/video/' . $html->getEsc($value) 
				. '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
		return new Raw($raw);
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\StatelessEditable::createOption()
	 */
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		return new VimeoOption($propertyName, $this->getLabelCode(), null,
				$this->isMandatory($entrySourceInfo), $this->getMaxlength(), null,
				array('placeholder' => $this->getLabelCode()));
	}	
}

class VimeoOption extends StringMag {
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		return new Raw('<span style="display: inline-block; line-height: 16px">http://vimeo.com/' . parent::createUiField($propertyPath, $view) . '</span>');
	}
	
}
