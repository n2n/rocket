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
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\entry\EiuObject;
use rocket\impl\ei\component\prop\relation\model\relation\MappedOneToCriteriaFactory;
use rocket\impl\ei\component\prop\relation\model\relation\RelationCriteriaFactory;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\model\relation\MappedRelationEiModificator;
use rocket\ei\util\entry\EiuEntry;
use rocket\impl\ei\component\prop\relation\model\relation\PlainMappedRelationEiModificator;
use rocket\impl\ei\component\prop\relation\model\relation\MasterRelationEiModificator;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\manage\frame\EiForkLink;

class Relation {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	
	/**
	 * @param RelationModel $relationModel
	 */
	function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	
	
	/**
	 * @param Eiu $eiu
	 * @return Eiu
	 */
	function createForkEiFrame(Eiu $eiu, EiForkLink $eiForkLink) {
		$targetEiuFrame = $this->relationModel->getTargetEiuEngine()->newFrame($eiForkLink);
		
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
		$targetPropInfo = $this->relationModel->getTargetPropInfo();
		
		if (null !== $targetPropInfo->eiPropPath) {
			$targetEiuFrame->setEiRelation($targetPropInfo->eiPropPath, $eiuFrame, $eiuEntry);
			
			if (!$eiuEntry->isDraft()) {
				$relationEiuObj = ($targetPropInfo->hasEntryValues() ? $eiuEntry : $eiuEntry->object());
				$targetEiuFrame->registerListener(new MappedRelationEiModificator($targetEiFrame,
						$relationEiuObj, $targetPropInfo->eiPropPath, $this->isSourceMany()));
			}
		} else if ($targetPropInfo->masterAccessProxy !== null) {
			$targetEiuFrame->registerListener(
					new PlainMappedRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
							$this->targetMasterAccessProxy, $this->isSourceMany()));
		}
		
		if ($this->getRelationEntityProperty()->isMaster() && !$eiuEntry->isDraft()) {
			$targetEiFrame->registerListener(new MasterRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
					$this->relationEiProp->getObjectPropertyAccessProxy(), $this->targetMany));
		}
	}
	
}