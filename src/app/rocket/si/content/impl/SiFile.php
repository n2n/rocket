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
namespace rocket\si\content\impl;

use n2n\io\managed\img\impl\ThSt;
use n2n\util\uri\Url;

class SiFile implements \JsonSerializable {
	private $name;
	private $url;
	private $thumbUrl;
	
	function __construct(string $name, Url $url = null) {
		$this->name = $name;
		$this->url = $url;
	}
	
	/**
	 * @return \n2n\util\uri\Url|null
	 */
	function getThumbUrl() {
		return $this->thumbUrl;
	}
	
	/**
	 * @param Url|null $thumbUrl
	 */
	function setThumbUrl(?Url $thumbUrl) {
		$this->thumbUrl = $thumbUrl;
	}
	
	function jsonSerialize() {
		return [
			'name' => '[missing file]',
			'url' => ($this->url !== null ? (string) $this->url : null),
			'thumbUrl' => ($this->thumbUrl !== null ? (string) $this->thumbUrl : null)
		];
	}
	
	/**
	 * @return \n2n\io\managed\img\ThumbStrategy
	 */
	static function getThumbStrategy() {
		return ThSt::crop(40, 30, true);
	}
	
	
}
