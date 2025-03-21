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
namespace rocket\ui\si\content\impl\meta;

use n2n\util\type\ArgUtils;

trait AddonsSiFieldTrait {
	/**
	 * @var SiCrumbGroup[]
	 */
	private $prefixAddons = [];
	/**
	 * @var SiCrumbGroup[]
	 */
	private $suffixAddons = [];
	
	/**
	 * @return SiCrumbGroup[]
	 */
	function getPrefixAddons(): array {
		return $this->prefixAddons;
	}
	
	/**
	 * @param SiCrumbGroup[] $prefixAddons
	 * @return self
	 */
	function setPrefixAddons(array $prefixAddons): static {
		ArgUtils::valArray($prefixAddons, SiCrumbGroup::class);
		$this->prefixAddons = $prefixAddons;
		return $this;
	}
	
	/**
	 * @return SiCrumbGroup[]
	 */
	function getSuffixAddons(): array {
		return $this->suffixAddons;
	}
	
	/**
	 * @param SiCrumbGroup[] $suffixAddons
	 * @return self
	 */
	function setSuffixAddons(array $suffixAddons): static {
		ArgUtils::valArray($suffixAddons, SiCrumbGroup::class);
		$this->suffixAddons = $suffixAddons;
		return $this;
	}
}