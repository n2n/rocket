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
namespace rocket\si\api;

use rocket\si\structure\SiCompactDeclaration;
use rocket\si\structure\SiBulkyDeclaration;
use rocket\si\content\SiEntry;
use rocket\si\content\SiPartialContent;

class SiGetResult implements \JsonSerializable {
	/**
	 * @var SiCompactDeclaration|null
	 */
	private $compactDeclaration = null;
	/**
	 * @var SiBulkyDeclaration|null
	 */
	private $bulkyDeclaration;
	/**
	 * @var SiEntry
	 */
	private $entry = [];
	/**
	 * @var SiPartialContent|null
	 */
	private $partialContent;
	
	function __construct() {
	}
	
	/**
	 * @return \rocket\si\structure\SiCompactDeclaration|null
	 */
	public function getCompactDeclaration() {
		return $this->compactDeclaration;
	}

	/**
	 * @param \rocket\si\structure\SiCompactDeclaration|null $compactDeclaration
	 */
	public function setCompactDeclaration(?SiCompactDeclaration $compactDeclaration) {
		$this->compactDeclaration = $compactDeclaration;
	}

	/**
	 * @return \rocket\si\structure\SiBulkyDeclaration|null
	 */
	public function getBulkyDeclaration() {
		return $this->bulkyDeclaration;
	}

	/**
	 * @param \rocket\si\structure\SiBulkyDeclaration|null $bulkyDeclaration
	 */
	public function setBulkyDeclaration(?SiBulkyDeclaration $bulkyDeclaration) {
		$this->bulkyDeclaration = $bulkyDeclaration;
	}

	/**
	 * @return \rocket\si\content\SiEntry
	 */
	public function getEntry() {
		return $this->entry;
	}

	/**
	 * @param \rocket\si\content\SiEntry|null $entries
	 */
	public function setEntry(?SiEntry $entry) {
		$this->entry = $entry;
	}

	/**
	 * @return \rocket\si\content\SiPartialContent|null
	 */
	public function getPartialContent() {
		return $this->partialContent;
	}

	/**
	 * @param \rocket\si\content\SiPartialContent|null $partialContent
	 */
	public function setPartialContent(?SiPartialContent $partialContent) {
		$this->partialContent = $partialContent;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function jsonSerialize() {
		return [
			'bulkyDeclaration' => $this->bulkyDeclaration,
			'compactDeclaration' => $this->compactDeclaration,
			'entry' => $this->entry,
			'partialContent' => $this->partialContent
		];
	}	
}
