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

class Relation {
	
	/**
	 * @var RelationEntityProperty
	 */
	private $relationEntityProperty;
	/**
	 * @var EiuEngine
	 */
	private $targetEiuEngine;
	/**
	 * @var bool
	 */
	private $sourceMany;
	/**
	 * @var bool
	 */
	private $targetMany;
	
	/**
	 * @var RelationEiProp
	 */
	private $targetMasterEiProp;
	
	/**
	 * @return RelationEiProp|null
	 */
	private function getTargetMasterEiProp() {
		if ($this->relationEntityProperty->isMaster()) {
			return null;
		}
		
		$this->$targetMasterEiProp == RelationUtils::deterTargetMas
	}
	
	/**
	 * @param Eiu $eiu
	 * @return Eiu
	 */
	function createForkEiFrame(Eiu $eiu, ControllerContext $controllerContext) {
		$targetEiuFrame = $this->targetEiuEngine->newFrame($controllerContext);
		
		if (null !== ($eiuObject = $eiu->object(false))) {
			$this->applyTargetCriteriaFactory($targetEiuFrame, $eiuObject);
		}
	}
	
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
	
	private function applyTargetModificators(EiuFrame $targetEiuFrame) {
		
	}
}

class RelationEiFrameSetup {
	/**
	 * @var EiuFrame
	 */
	private $eiuFrame;
	
	function __construct(EiuFrame $eiuFrame, RelationEntityProperty $relationEntityProperty) {
		$this->eiuFrame = $eiuFrame;
		$this->relationEntityProperty = $relationEntityProperty;
	}
	
	
}