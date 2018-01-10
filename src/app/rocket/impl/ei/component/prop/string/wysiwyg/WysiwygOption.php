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
namespace rocket\impl\ei\component\prop\string\wysiwyg;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\web\ui\UiComponent;

class WysiwygOption extends StringMag {
	
	private $inputAttrs;
	
	private $mode;
	private $bbcode;
	private $tableEditing;
	/**
	 * @var array
	 */
	private $linkConfigurations;
	
	/**
	* @var \rocket\impl\ei\component\prop\string\wysiwyg\WysiwygCssConfig
	*/
	private $cssConfiguration;
	
	public function __construct($label, $default = null, $mandatory = false, $maxlength = null, array $inputAttrs = null, 
			$mode = self::MODE_NORMAL, $bbcode = false, $tableEditing = false, array $linkConfigurations = null, WysiwygCssConfig $cssConfiguration = null) {
		parent::__construct($label, $default, $mandatory, $maxlength, true, $inputAttrs);
		
		$this->inputAttrs = $inputAttrs;
		$this->mode = $mode;
		$this->bbcode = $bbcode;
		$this->tableEditing = $tableEditing;
		$this->linkConfigurations = $linkConfigurations;
		$this->cssConfiguration = $cssConfiguration;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView): UiComponent {
		$wysiwygHtml = new WysiwygHtmlBuilder($htmlView);
		return $wysiwygHtml->getWysiwygEditor($propertyPath, $this->mode, $this->bbcode, false, $this->tableEditing, $this->linkConfigurations, $this->cssConfiguration, $this->inputAttrs);
	}
}
