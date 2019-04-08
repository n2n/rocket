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
use rocket\si\structure\SiCompactDeclaration;

class ListSiZone implements SiZone {
	private $apiUrl;
	private $pageSize;
	private $siCompactContent;
	
	/**
	 * @param Url $apiUrl
	 * @param int $pageSize
	 * @param SiCompactDeclaration $siCompactContent
	 */
	public function __construct(Url $apiUrl, int $pageSize, SiCompactDeclaration $siCompactContent = null) {
		$this->apiUrl = $apiUrl;
		$this->pageSize = $pageSize;
		$this->siCompactContent = $siCompactContent;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\context\SiZone::getTypeName()
	 */
	public function getTypeName(): string {
		return 'list';
	}
	
	/**
	 * @return int
	 */
	public function getPageSize() {
		return $this->pageSize;
	}
	
	/**
	 * @param int $pageSize
	 * @return \rocket\si\context\impl\ListSiZone
	 */
	public function setPageSize(int $pageSize) {
		$this->pageSize = $pageSize;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\context\SiZone::getData()
	 */
	public function getData(): array {
		return [
			'pageSize' => $this->pageSize,
			'siCompactContent' => $this->siCompactContent
		];
	}
}
