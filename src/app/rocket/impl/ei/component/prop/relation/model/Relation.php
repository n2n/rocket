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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\ei\util\Eiu;
use rocket\ei\util\spec\EiuEngine;
use n2n\web\http\controller\ControllerContext;
use rocket\ei\util\frame\EiuFrame;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\util\entry\EiuObject;
use rocket\impl\ei\component\prop\relation\model\relation\MappedOneToCriteriaFactory;
use rocket\impl\ei\component\prop\relation\model\relation\RelationCriteriaFactory;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use rocket\ei\EiPropPath;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\CastUtils;
use n2n\impl\persistence\orm\property\relation\MappedRelation;
use rocket\ei\mask\EiMask;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\model\relation\MappedRelationEiModificator;
use rocket\ei\util\entry\EiuEntry;
use rocket\impl\ei\component\prop\relation\model\relation\PlainMappedRelationEiModificator;
use rocket\impl\ei\component\prop\relation\model\relation\MasterRelationEiModificator;
use n2n\util\type\TypeUtils;
use rocket\impl\ei\component\prop\relation\conf\RelationEiPropConfigurator;
use n2n\persistence\orm\CascadeType;

class Relation {
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
	 * @var bool
	 */
	private $embedded;
	
	/**
	 * @var EiuEngine
	 */
	private $targetEiuEngine;
	/**
	 * @var TargetPropInfo
	 */
	private $targetPropInfo;
	
	private function __construct(RelationEntityProperty $relationEntityProperty, bool $sourceMany, bool $targetMany, 
			bool $embedded) {
		$this->sourceMany = $sourceMany;
		$this->targetMany = $targetMany;
		$this->embedded = $embedded;
	}
	
	/**
	 * @param EiuEngine $targetEiuEngine
	 */
	function init(EiuEngine $targetEiuEngine) {
		$this->targetEiuEngine = $targetEiuEngine;
		$this->targetPropInfo = RelationUtils::deterTargetPropInfo($this->relationEntityProperty, $targetEiuEngine);
		if ($this->embedded) {
			RelationUtils::validateEmbedded($this->relationEntityProperty, $this->sourceMany);
		}
	}
	
	function ensureInit() {
		if ($this->targetEiuEngine !== null) return;
		
		throw new IllegalStateException('Relation not initialized.');
	}
	
	/**
	 * @param Eiu $eiu
	 * @return Eiu
	 */
	function createForkEiFrame(Eiu $eiu, ControllerContext $controllerContext) {
		$this->ensureInit();
		$targetEiuFrame = $this->targetEiuEngine->newFrame($controllerContext);
		
		if (null !== ($eiuEntry = $eiu->entry(false))) {
			$this->applyTargetCriteriaFactory($targetEiuFrame, $eiuEntry);
		}
		
		return $targetEiuFrame->getEiFrame();
	}
	
	/**
	 * @param EiuFrame $targetEiuFrame
	 * @param EiuObject $eiuObject
	 */
	private function applyTargetCriteriaFactory(EiuFrame $targetEiuFrame, EiuObject $eiuObject) {
		if ($eiuObject->isNew()) {
			return;
		}
		
		if (!$this->relationEntityProperty->isMaster() && !$this->isSourceMany()) {
			$targetEiuFrame->setCriteriaFactory(new MappedOneToCriteriaFactory(
					$this->getRelationEntityProperty()->getRelation(),
					$eiuObject->getEntityObj()));
			return;
		}
		
		$targetEiuFrame->setCriteriaFactory(new RelationCriteriaFactory($this->relationEntityProperty, 
				$eiuObject->getEntityObj()));
	}
	
	/**
	 * @param EiuFrame $targetEiuFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuEntry $eiuEntry
	 */
	private function applyTargetModificators(EiuFrame $targetEiuFrame, EiuFrame $eiuFrame, EiuEntry $eiuEntry) {
		$targetEiFrame = $eiuFrame->getEiFrame();
		
		if (null !== $this->targetPropInfo->eiPropPath) {
			$targetEiuFrame->setEiRelation($this->targetPropInfo->eiPropPath, $eiuFrame, $eiuEntry);
			
			if (!$eiuEntry->isDraft()) {
				$relationEiuObj = ($this->targetPropInfo->hasEntryValues() ? $eiuEntry : $eiuEntry->object());
				$targetEiuFrame->registerListener(new MappedRelationEiModificator($targetEiFrame,
						$relationEiuObj, EiPropPath::from($targetEiProp), $this->isSourceMany()));
			}
		} 
		
		if ($this->targetMasterAccessProxy !== null) {
			$targetEiuFrame->registerListener(
					new PlainMappedRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
							$this->targetMasterAccessProxy, $this->isSourceMany()));
		}
		
		if ($this->getRelationEntityProperty()->isMaster() && !$eiuEntry->isDraft()) {
			$targetEiFrame->registerListener(new MasterRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
					$this->relationEiProp->getObjectPropertyAccessProxy(), $this->targetMany));
		}
	}
	
	static function createManyToOne(RelationEntityProperty $relationEntityProperty, bool $embedded) {
		$configurator = new asdf();
		$relation = new Relation($relationEntityProperty, true, false);
		$configurator->setModel();
		return $relation;
	}
}

class RelationUtils {
	/**
	 * @throws InvalidEiComponentConfigurationException
	 */
	function deterTargetPropInfo(RelationEntityProperty $entityProperty, EiuEngine $targetEiuEngine) {
		$entityProperty = $this->getRelationEntityProperty();
		
		$targetEiMask = $targetEiuEngine->getEiEngine()->getEiMask();
		
		if (!$entityProperty->isMaster()) {
			return self::deterTargetMaster($entityProperty, $targetEiMask);
		}
		
		return self::deterTargetMapped($entityProperty, $targetEiMask);
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @return \rocket\impl\ei\component\prop\relation\model\TargetPropInfo
	 */
	private function deterTargetMapped(RelationEntityProperty $entityProperty, EiMask $targetEiMask) {
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (!($targetEiProp instanceof RelationEiProp)) continue;
			
			$targetRelationEntityProperty = $targetEiProp->getRelationEntityProperty();
			
			$targetRelation = $targetRelationEntityProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($relationEntityProperty)) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp));
			}
		}
		
		return new TargetPropInfo();
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @return \rocket\impl\ei\component\prop\relation\model\TargetPropInfo
	 */
	private function deterTargetMaster(RelationEntityProperty $entityProperty, EiMask $targetEiMask) {
		$mappedRelation = $entityProperty->getRelation();
		CastUtils::assertTrue($mappedRelation instanceof MappedRelation);
		
		$targetEntityProperty = $mappedRelation->getTargetEntityProperty();
		
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (($targetEiProp instanceof RelationEiProp)
					&& $targetEntityProperty->equals($targetEiProp->getRelationEntityProperty())) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp));
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
	
	static function validateEmbedded(RelationEntityProperty $entityProperty, bool $sourceMany, RelationModel $relationModel) {
		if (!($entityProperty->getRelation()->getCascadeType() & CascadeType::PERSIST)) {
			$entityProperty = $this->getRelationEiProp()->getEntityProperty();
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
		
		if (!$relationModel->getOrphansAllowed()) {
			throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
					. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
					. ' which removes orphans or an EiProp configuration with '
					. RelationEiPropConfigurator::ATTR_ORPHANS_ALLOWED_KEY . '=true.');
		}
		
		if (!$entityProperty->isMaster() && !$sourceMany
				&& !$this->getTargetMasterAccessProxy()->getConstraint()->allowsNull()) {
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