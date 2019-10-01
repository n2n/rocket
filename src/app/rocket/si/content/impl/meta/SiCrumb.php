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
namespace rocket\si\content\impl\meta;

class SiCrumb implements \JsonSerializable {
	const TYPE_ICON = 'icon';
	const TYPE_LABEL = 'label';
	
	protected $type;
	protected $label;
	protected $iconClass;
	
	/**
	 * @return string
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	function getIconClass() {
		return $this->iconClass;
	}
	
	/**
	 * @return array
	 */
	function jsonSerialize() {
		return [
			'type' => $this->type,
			'label' => $this->label,
			'iconClass' => $this->iconClass
		];
	}
	
	/**
	 * @param string $label
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	static function createLabel(string $label) {
		$addon = new SiCrumb();
		$addon->type = self::TYPE_LABEL;
		$addon->label = $label;
		return $addon;
	}
	
	/**
	 * @param string $iconClass
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	static function createIcon(string $iconClass) {
		$addon = new SiCrumb();
		$addon->type = self::TYPE_ICON;
		$addon->iconClass = $iconClass;
		return $addon;
	}
}
