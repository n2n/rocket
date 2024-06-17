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

class SiDeclaration implements \JsonSerializable {
//	/**
//	 * @var SiStyle
//	 */
//	private $style;
	/**
	 * @var SiMask[]
	 */
	private array $masks = [];

	/**
	 * @param SiMask[] $masks
	 */
	function __construct(array $masks = []) {
		$this->setMasks($masks);
	}
	
	/**
	 * @param SiMask[] $masks
	 * @return static
	 */
	function setMasks(array $masks): static {
		ArgUtils::valArray($masks, SiMask::class);
		$this->masks = [];
		
		foreach ($masks as $mask) {
			$this->addMask($mask);
		}
		return $this;
	}

	function addMask(SiMask $mask): static {
// 		if (empty($this->masks) && !$mask->hasStructureDeclarations()) {
// 			throw new \InvalidArgumentException('First TypeDeclaration need StructureDeclarations');
// 		}
		
		if (empty($this->masks) && !$mask->getMask()->hasProps()) {
			throw new \InvalidArgumentException('First TypeDeclaration needs to have SiProps.');
		}
		
		$this->masks[] = $mask;
		return $this;
	}
	
	/**
	 * @return SiMask[]
	 */
	function getMasks(): array {
		return $this->masks;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'masks' => $this->masks
		];
	}
}