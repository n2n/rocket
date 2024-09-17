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
namespace rocket\ui\si\content;

use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;

class SiPartialContent {
	private $count;
	private $offset = 0;
	private $valueBoundaries;
	

	/**
	 * @param int $count
	 * @param SiValueBoundary[] $entries
	 */
	function __construct(int $count, array $entries = []) {
		$this->count = $count;
		$this->setValueBoundaries($entries);
	}
	
	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}
	
	/**
	 * @param int $offset
	 */
	public function setOffset(int $offset) {
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getCount() {
		return $this->count;
	}
	
	/**
	 * @param int $count
	 * @return \rocket\si\content\SiPartialContent
	 */
	function setCount(int $count) {
		$this->count = $count;
		return $this;
	}

	/**
	 * @param SiValueBoundary[] $valueBoundaries
	 * @return static
	 */
	function setValueBoundaries(array $valueBoundaries): static {
		ArgUtils::valArray($valueBoundaries, SiValueBoundary::class);
		$this->valueBoundaries = $valueBoundaries;
		return $this;
	}
	
	/**
	 * @return SiValueBoundary[]
	 */
	function getValueBoundaries(): array {
		return $this->valueBoundaries;
	}

	function toDataStruct(N2nContext $n2nContext): mixed {
		return [
			'siValueBoundaries' => $this->valueBoundaries === null
					? null
					: array_map(fn (SiValueBoundary $s) => $s->toJsonStruct($n2nContext), $this->valueBoundaries),
			'count' => $this->count,
			'offset' => $this->offset
		];
	}
}