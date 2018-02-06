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
namespace rocket\impl\ei\component\prop\string\cke\model;

class CkeStyle {
	private $name;
	private $element;
	private $attrs;
	private $styles;
	
	public function __construct($name, $element, array $attrs = null, array $styles = null) {
		$this->name = $name;
		$this->element = strval($element);
		$this->attrs = $attrs;
		$this->styles = $styles;
	}
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getElement() {
		return $this->element;
	}

	public function setElement($element) {
		$this->element = $element;
	}

	public function getAttrs() {
		return (array) $this->attrs;
	}

	public function setAttrs(array $attrs) {
		$this->attrs = $attrs;
	}

	public function getStyles() {
		return (array) $this->styles;
	}

	public function setStyles(array $styles) {
		$this->styles = $styles;
	}
	
	public function getValueForJsonEncode() {
		$ret = array('name' => $this->name, 'element' => $this->element);
		if (null != $this->attrs) {
			$ret['attributes'] = $this->attrs;
		}
		if (null != $this->styles) {
			$ret['styles'] = $this->styles;
		}
		return $ret;
	}
}
