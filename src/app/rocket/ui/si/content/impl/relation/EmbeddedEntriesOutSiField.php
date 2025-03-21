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
namespace rocket\ui\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\content\impl\OutSiFieldAdapter;

class EmbeddedEntriesOutSiField extends OutSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
	/**
	 * @var SiEmbeddedEntry[]
	 */
	private $values;
	/**
	 * @var bool
	 */
	private $reduced = false;
	
	/**
	 * @param string $typeCateogry
	 * @param Url $apiUrl
	 * @param SiEmbeddedEntryFactory $inputHandler
	 * @param SiEmbeddedEntry[] $values
	 */
	function __construct(SiFrame $frame, array $values = []) {
		$this->frame = $frame;
		$this->setValues($values);
	}
	
	/**
	 * @param SiEmbeddedEntry[] $values
	 * @return EmbeddedEntriesOutSiField
	 */
	function setValues(array $values) {
		ArgUtils::valArray($values, SiEmbeddedEntry::class);
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @return boolean
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 * @return EmbeddedEntriesOutSiField
	 */
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entries-out';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'values' => array_map(fn (SiEmbeddedEntry $e) => $e->toJsonStruct($n2nContext), $this->values),
			'frame' => $this->frame,
			'reduced' => $this->reduced,
			...parent::toJsonStruct($n2nContext)
		];
	}
}
