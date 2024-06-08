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
namespace rocket\ui\si\meta;

use n2n\util\type\ArgUtils;

class SiTypeContext implements \JsonSerializable {
	private $typeId = null;
	private $maskIds = [];
	private $treeMode = false;
	
	/**
	 * @param string $typeId
	 * @param array $maskIds
	 */
	function __construct(string $typeId, array $maskIds) {
		$this->typeId = $typeId;
		ArgUtils::valArray($maskIds, 'string');
		$this->maskIds = array_values($maskIds);
	}
	
	/**
	 * @param string $maskId
	 * @return bool
	 */
	function containsMaskId(string $maskId) {
		return in_array($maskId, $this->maskIds);
	}
	
	/**
	 * @param bool $treeMode
	 * @return SiTypeContext
	 */
	function setTreeMode(bool $treeMode) {
		$this->treeMode = $treeMode;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isTreeMode() {
		return $this->treeMode;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'typeId' => $this->typeId,
			'entryIds' => $this->maskIds,
			'treeMode' => $this->treeMode
		];
	}
}