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
namespace rocket\ui\si\content\impl\split;

use n2n\util\uri\Url;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\meta\SiStyle;
use n2n\core\container\N2nContext;

class SiSplitContent {
	private $label;
	private $shortLabel;
	
	private $apiGetUrl;
	private $entryId;
	private $style;
	private $propIds = null;
// 	/**
// 	 * @var SiDeclaration
// 	 */
// 	private $declaration;
	/**
	 * @var SiValueBoundary
	 */
	private $entry;
	
	private function __construct() {
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	function getShortLabel() {
		return $this->shortLabel;
	}
	
	/**
	 * @param string $shortLabel
	 * @return SiSplitContent
	 */
	function setShortLabel(?string $shortLabel) {
		$this->shortLabel = $shortLabel;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getPropIds() {
		return $this->propIds;
	}
	
	/**
	 * @param string[] $propIds
	 * @return SiSplitContent
	 */
	function setPropIds(array $propIds) {
		$this->propIds = array_values($propIds);
		return $this;
	}
	
	/**
	 * @return \rocket\ui\si\content\SiValueBoundary|null
	 */
	function getEntry() {
		return $this->entry;
	}
	
	function toJsonStruct(N2nContext $n2nContext): mixed {
		$data = [ 'label' => $this->label, 'shortLabel' => $this->shortLabel ?? $this->label ];
		
// 		if ($this->apiUrl !== null) {
			$data['apiGetUrl'] = (string) $this->apiGetUrl;
			$data['entryId'] = $this->entryId;
			$data['propIds'] = $this->propIds;
			$data['style'] = $this->style;
// 		}
		
// 		if ($this->entry !== null) {
// 			$data['declaration'] = $this->declaration;
			$data['valueBoundary'] = $this->entry?->toJsonStruct($n2nContext);
// 		}
		
		return $data;
	}
	
	static function createUnavailable(string $label): SiSplitContent {
		$split = new SiSplitContent();
		$split->label = $label;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param Url $apiGetUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return SiSplitContent
	 */
	static function createLazy(string $label, Url $apiGetUrl, ?string $entryId, SiStyle $style) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->apiGetUrl = $apiGetUrl;
		$split->entryId = $entryId;
		$split->style = $style;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param SiValueBoundary $entry
	 * @return SiSplitContent
	 */
	static function createValueBoundary(string $label, SiValueBoundary $entry): SiSplitContent {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->entry = $entry;
		return $split;
	}
}
