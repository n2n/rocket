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
namespace rocket\impl\ei\component\prop\relation\conf;

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\EiPropPath;
use n2n\util\type\ArgUtils;

class RelationModel {
	const MODE_SELECT = 'select';
	const MODE_EMBEDDED = 'embedded';
	const MODE_PICK = 'pick';
	
	/**
	 * @var RelationEntityProperty
	 */
	private $relationEntityProperty;
	/**
	 * @var bool
	 */
	private $sourceMany;
	/**
	 * @var bool
	 */
	private $targetMany;
	/**
	 * @var string
	 */
	private $mode;
	
	// Select
	
	private $filtered = true;
	private $hiddenIfTargetEmpty = false;
	
	// Embedded
	
	private $orphansAllowed = false;
	private $reduced = true;
	private $removable = true;
	
	// ToMany
	
	private $min = null;
	private $max = null;
	
	// EmbeddedToMany
	
	private $tragetOrderEiPropPath = null; 

	/**
	 * @param RelationEntityProperty $relationEntityProperty
	 * @param bool $sourceMany
	 * @param bool $targetMany
	 * @param bool $embedded
	 */
	function __construct(RelationEntityProperty $relationEntityProperty, bool $sourceMany, bool $targetMany,
			string $mode) {
		$this->relationEntityProperty = $relationEntityProperty;
		$this->sourceMany = $sourceMany;
		$this->targetMany = $targetMany;

		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
	}
	
	/**
	 * @return string[]
	 */
	static function getModes() {
		return [self::MODE_SELECT, self::MODE_EMBEDDED, self::MODE_PICK];
	}
	
	/**
	 * @return \n2n\impl\persistence\orm\property\RelationEntityProperty
	 */
	function getRelationEntityProperty() {
		return $this->relationEntityProperty;
	}
	
	/**
	 * @return boolean
	 */
	function isSourceMany() {
		return $this->sourceMany;
	}
	
	/**
	 * @return boolean
	 */
	function isTargetMany() {
		return $this->targetMany;
	}
	
	/**
	 * @return boolean
	 */
	function isMaster() {
		return $this->relationEntityProperty->isMaster();
	}
	
	/**
	 * @return boolean
	 */
	function isSelect() {
		return $this->mode == self::MODE_SELECT;
	}
	
	/**
	 * @return boolean
	 */
	function isEmbedded() {
		return $this->mode == self::MODE_EMBEDDED;
	}
	
	/**
	 * @return boolean
	 */
	function isPick() {
		return $this->mode == self::MODE_PICK;
	}
	
	/**
	 * @return boolean
	 */
	function isFiltered() {
		return $this->filtered;
	}
	
	/**
	 * @param boolean $filtered
	 */
	function setFiltered(bool $filtered) {
		$this->filtered = $filtered;
	}
	
	/**
	 * @return boolean
	 */
	function isHiddenIfTargetEmpty() {
		return $this->hiddenIfTargetEmpty;
	}
	
	/**
	 * @param boolean $hiddenIfTargetEmpty
	 */
	function setHiddenIfTargetEmpty($hiddenIfTargetEmpty) {
		$this->hiddenIfTargetEmpty = $hiddenIfTargetEmpty;
	}
	
	/**
	 * @return boolean
	 */
	function isOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	/**
	 * @param boolean $orphansAllowed
	 */
	function setOrphansAllowed($orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	/**
	 * @return boolean
	 */
	function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 */
	function setReduced($reduced) {
		$this->reduced = $reduced;
	}
	
	/**
	 * @return boolean
	 */
	function isRemovable() {
		return $this->removable;
	}
	
	/**
	 * @param boolean $removable
	 */
	function setRemovable($removable) {
		$this->removable = $removable;
	}
	
	/**
	 * @return int|null
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int|null $min
	 */
	function setMin(?int $min) {
		$this->min = $min;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 */
	function setMax(?int $max) {
		$this->max = $max;
	}
	
	/**
	 * @return EiPropPath|null
	 */
	function getTragetOrderEiPropPath() {
		return $this->tragetOrderEiPropPath;
	}
	
	/**
	 * @param EiPropPath|null $tragetOrderEiPropPath
	 */
	function setTragetOrderEiPropPath(?EiPropPath $tragetOrderEiPropPath) {
		$this->tragetOrderEiPropPath = $tragetOrderEiPropPath;
	}
}