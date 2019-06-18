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
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\mask\EiMask;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\impl\persistence\orm\property\relation\MappedRelation;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\util\type\CastUtils;
use n2n\persistence\orm\CascadeType;
use n2n\util\type\TypeUtils;
use n2n\util\ex\IllegalStateException;

class RelationModel {
	const MODE_SELECT = 'select';
	const MODE_EMBEDDED = 'embedded';
	const MODE_PICK = 'pick';
	const MODE_INTEGRATED = 'integrated';
	
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
	
	// Finalize
	
	/**
	 * @var EiuEngine
	 */
	private $targetEiuEngine;
	/**
	 * @var TargetPropInfo
	 */
	private $targetPropInfo;
	

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
		return [self::MODE_SELECT, self::MODE_EMBEDDED, self::MODE_PICK, self::MODE_INTEGRATED];
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
	
	/**
	 * @param EiuEngine $targetEiuEngine
	 */
	function finalize(EiuEngine $targetEiuEngine) {
		$rf = new RelationFinalizer($this);
		
		$this->targetPropInfo = $rf->deterTargetPropInfo($targetEiuEngine);
		if ($this->isEmbedded()) {
			$rf->validateEmbedded($targetEiuEngine);
		}
		$this->targetEiuEngine = $targetEiuEngine;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\conf\TargetPropInfo
	 */
	function getTargetPropInfo() {
		IllegalStateException::assertTrue($this->targetPropInfo !== null);
		return $this->targetPropInfo;
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	function getTargetEiuEngine() {
		IllegalStateException::assertTrue($this->targetEiuEngine !== null);
		return $this->targetEiuEngine;
	}
}


class RelationFinalizer {
	private $relationModel;
	
	/**
	 * @param RelationModel $relationModel
	 */
	function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	
	/**
	 * @throws InvalidEiComponentConfigurationException
	 */
	function deterTargetPropInfo(EiuEngine $targetEiuEngine) {
		$targetEiMask = $targetEiuEngine->getEiEngine()->getEiMask();
		
		if (!$this->relationModel->isMaster()) {
			return self::deterTargetMaster($targetEiMask);
		}
		
		return self::deterTargetMapped($targetEiMask);
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @return TargetPropInfo
	 */
	private function deterTargetMapped(EiMask $targetEiMask) {
		$relationEntityProperty = $this->relationModel->getRelationEntityProperty();
		
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (!($targetEiProp instanceof RelationEiProp)) continue;
			
			$targetRelationEntityProperty = $targetEiProp->getRelationEntityProperty();
			
			$targetRelation = $targetRelationEntityProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($relationEntityProperty)) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp), 
						$targetEiProp->getObjectPropertyAccessProxy());
			}
		}
		
		return new TargetPropInfo();
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @return TargetPropInfo
	 */
	private function deterTargetMaster(EiMask $targetEiMask) {
		$mappedRelation = $this->relationModel->getRelationEntityProperty()->getRelation();
		CastUtils::assertTrue($mappedRelation instanceof MappedRelation);
		
		$targetEntityProperty = $mappedRelation->getTargetEntityProperty();
		
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (($targetEiProp instanceof RelationEiProp)
					&& $targetEntityProperty->equals($targetEiProp->getRelationEntityProperty())) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp), $targetEiProp->getObjectPropertyAccessProxy());
			}
		}
		
		$targetClass = $targetEiMask->getEiType()->getEntityModel()->getClass();
		$propertiesAnalyzer = new PropertiesAnalyzer($targetClass);
		try {
			return new TargetPropInfo(null, $propertiesAnalyzer->analyzeProperty($targetEntityProperty->getName()));
		} catch (ReflectionException $e) {
			throw new InvalidEiComponentConfigurationException('No Target master property not accessible: '
					. $targetEntityProperty, 0, $e);
		}
	}
	
	function validateEmbedded() {
		$entityProperty = $this->relationModel->getRelationEntityProperty();
		
		if (!($entityProperty->getRelation()->getCascadeType() & CascadeType::PERSIST)) {
			throw new InvalidEiComponentConfigurationException(
					'EiProp requires an EntityProperty which cascades persist: '
							. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(),
									$entityProperty->getName()));
		}
		
		// 		if ($this->isDraftable() && !$this->isJoinTableRelation($this)) {
		// 			throw new InvalidEiComponentConfigurationException(
		// 					'Only EiProps of properties with join table relations can be drafted.');
		// 		}
		
		// reason to remove: orphans should never remain in db on embeddedeiprops
		if ($entityProperty->getRelation()->isOrphanRemoval()) {
			return;
		}
		
		if (!$this->relationModel->isOrphansAllowed()) {
			throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
					. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
					. ' which removes orphans or an EiProp configuration with '
					. RelationEiPropConfigurator::ATTR_ORPHANS_ALLOWED_KEY . '=true.');
		} 
		
		if (!$entityProperty->isMaster() && !$this->relationModel->isSourceMany()
				&& !$this->relationModel->getTargetPropInfo()->masterAccessProxy->getConstraint()->allowsNull()) {
			throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
					. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
					. ' which removes orphans or target ' . $this->getTargetMasterAccessProxy()
					. ' must accept null.');
		}
	}
}

class TargetPropInfo {
	/**
	 * @var EiPropPath|null
	 */
	public $eiPropPath;
	/**
	 * @var AccessProxy|null
	 */
	public $masterAccessProxy;
	
	function __construct(EiPropPath $eiPropPath = null, AccessProxy $masterAccessProxy = null) {
		$this->eiPropPath = $eiPropPath;
		$this->masterAccessProxy = $masterAccessProxy;
	}
	
}