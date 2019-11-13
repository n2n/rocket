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
namespace rocket\si\content\impl\basic;

use n2n\util\uri\Url;
use rocket\si\meta\SiDeclaration;
use rocket\si\content\SiPartialContent;
use rocket\si\content\SiComp;

class EntriesListSiComp implements SiComp {
	private $apiUrl;
	private $pageSize;
	private $declaration;
	private $partialContent;
	
	/**
	 * @param Url $apiUrl
	 * @param int $pageSize
	 * @param SiDeclaration $siCompactContent
	 */
	public function __construct(Url $apiUrl, int $pageSize, SiDeclaration $declaration = null,
			SiPartialContent $partialContent = null) {
		$this->apiUrl = $apiUrl;
		$this->pageSize = $pageSize;
		$this->declaration = $declaration;
		$this->partialContent = $partialContent;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiComp::getTypeName()
	 */
	public function getTypeName(): string {
		return 'entries-list';
	}
	
	/**
	 * @return Url
	 */
	public function getApiUrl(): Url {
		return $this->apiUrl;
	}
	
	/**
	 * @return int
	 */
	public function getPageSize() {
		return $this->pageSize;
	}
	
	/**
	 * @param int $pageSize
	 * @return \rocket\si\content\impl\basic\EntriesListSiComp
	 */
	public function setPageSize(int $pageSize) {
		$this->pageSize = $pageSize;
		return $this;
	}
	
	/**
	 * @param SiPartialContent|null $partialContent
	 */
	public function setPartialContent(?SiPartialContent $partialContent) {
		$this->partialContent = $partialContent;
	}
	
	/**
	 * @return \rocket\si\content\SiPartialContent
	 */
	public function getPartialContent() {
		return $this->partialContent;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiComp::getData()
	 */
	public function getData(): array {
		return [
			'apiUrl' => (string) $this->apiUrl,
			'pageSize' => $this->pageSize,
			'declaration' => $this->declaration,
			'partialContent' => $this->partialContent
		];
	}

}