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
namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\component\field\impl\string\cke\ui\CkeHtmlBuilder;
use rocket\spec\ei\component\field\impl\string\cke\ui\Cke;
use n2n\impl\web\dispatch\mag\model\StringMag;

class CkeMag extends StringMag {
	private $mode;
	private $bbcode;
	private $tableEditing;
	private $ckeLinkProviderLookupIds;
	private $ckeCssCssConfigLookupId;
	
	public function __construct(string $propertyName, $label, $value = null, bool $mandatory = false, 
			int $maxlength = null, array $inputAttrs = null, string $mode = self::MODE_NORMAL, bool $bbcode = false, 
			bool $tableEditing = false, array $ckeLinkProviderLookupIds, string $ckeCssConfigLookupId = null) {
		parent::__construct($propertyName, $label, $value, $mandatory, $maxlength, true, $inputAttrs);
		
		$this->mode = $mode;
		$this->bbcode = $bbcode;
		$this->tableEditing = $tableEditing;
		$this->ckeLinkProviderLookupIds = $ckeLinkProviderLookupIds;
		$this->ckeCssCssConfigLookupId = $ckeCssConfigLookupId;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView): UiComponent {
		$ckeHtml = new CkeHtmlBuilder($htmlView);
		return $ckeHtml->getEditor($propertyPath, $this->mode, $this->bbcode, false, $this->tableEditing, 
				$this->ckeLinkProviderLookupIds, $this->ckeCssCssConfigLookupId, $this->getInputAttrs());
	}
}
