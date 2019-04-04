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
namespace rocket\si\context\impl;

use rocket\si\context\SiZone;
use n2n\util\uri\Url;
use rocket\si\content\SiCompactContent;

class ListSiZone implements SiZone {
	private $apiUrl;
	private $siCompactContent;
	
	public function __construct(Url $apiUrl, SiCompactContent $siCompactContent) {
		$this->apiUrl = $apiUrl;
		$this->siCompactContent = $siCompactContent;
	}
	
	public function getTypeName(): string {
		return 'list';
	}
	
	public function getData(): array {
		return [ 
			'siCompactContent' => $this->siCompactContent
		];
	}
}
