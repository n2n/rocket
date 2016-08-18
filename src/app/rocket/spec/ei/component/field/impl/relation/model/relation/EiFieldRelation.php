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
namespace rocket\spec\ei\component\field\impl\relation\model\relation;

use rocket\spec\ei\component\field\impl\relation\RelationEiField;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\property\impl\RelationEntityProperty;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\component\field\impl\relation\command\RelationEiCommand;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\persistence\orm\property\impl\relation\MappedRelation;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\EiRelation;
use n2n\web\http\controller\ControllerContext;
use n2n\persistence\orm\CascadeType;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\EiSpec;
use n2n\util\uri\Path;
use rocket\spec\ei\manage\EiStateFactory;
use rocket\spec\ei\component\field\impl\relation\command\RelationAjahEiCommand;
use rocket\spec\ei\component\field\impl\relation\command\RelationAjahController;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\security\InaccessibleControlException;
use rocket\spec\ei\component\field\impl\relation\command\EmbeddedEditPseudoCommand;
use n2n\util\uri\Url;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\EiUtils;
use rocket\spec\ei\EiThing;
use n2n\reflection\CastUtils;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use n2n\web\http\HttpContext;
use n2n\l10n\N2nLocale;

abstract class EiFieldRelation {
	protected $targetEiSpec;
	protected $targetEiMask;
	protected $targetMasterEiField;
	
	protected $relationEiField;
	protected $sourceMany;
	protected $targetMany;	
	
	protected $filtered = true;
	
	protected $relationEiCommand;
	protected $embeddedEditEiCommand;
	protected $relationAjahEiCommand;
	
	public function __construct(RelationEiField $relationEiField, bool $sourceMany, bool $targetMany) {
		$this->relationEiField = $relationEiField;
		$this->sourceMany = $sourceMany;
		$this->targetMany = $targetMany;
	}
	
	public function getRelationEiField(): RelationEiField {
		return $this->relationEiField;
	}
	
	public function isSourceMany(): bool {
		return $this->sourceMany;
	}
	
	public function isTargetMany(): bool {
		return $this->targetMany;
	}
	
	public function getTargetEiSpec(): EiSpec {
		if ($this->targetEiSpec === null) {
			throw new IllegalStateException(get_class($this->relationEiField) . ' not set up');
		}
		return $this->targetEiSpec;
	}
	
	public function getTargetEiMask(): EiMask {
		if ($this->targetEiMask === null) {
			throw new IllegalStateException(get_class($this->relationEiField) . ' not set up');
		}
		return $this->targetEiMask;
	}
	
	
	private function initTargetMasterEiField() {
		$entityProperty = $this->getRelationEntityProperty();
		if ($entityProperty->isMaster()) return;
		
		$mappedRelation = $entityProperty->getRelation();
		CastUtils::assertTrue($mappedRelation instanceof MappedRelation);
		
		$targetEntityProperty = $mappedRelation->getTargetEntityProperty();
		
		foreach ($this->getTargetEiMask()->getEiEngine()->getEiFieldCollection() as $targetEiField) {
			if ($targetEiField instanceof RelationEiField 
					&& $targetEntityProperty->equals($targetEiField->getEntityProperty())) {
				$this->targetMasterEiField = $targetEiField;
				return;
			}
		}
		
		throw new InvalidEiComponentConfigurationException('No Target master EiField defined for ' . $targetEntityProperty);
		
	}
	
	public function init(EiSpec $targetEiSpec, EiMask $targetEiMask) {
		$this->targetEiSpec = $targetEiSpec;
		$this->targetEiMask = $targetEiMask;
		
		$this->initTargetMasterEiField();		
		
		// supreme EiEngine to make command available in EiStates with super context EiSpecs.
		$superemeEiEngine = $this->relationEiField->getEiEngine()->getSupremeEiEngine();
		$this->relationEiCommand = new RelationEiCommand($this);
		$superemeEiEngine->getEiCommandCollection()->add($this->relationEiCommand);
				
		$this->relationAjahEiCommand = new RelationAjahEiCommand($this);
		$targetEiMask->getEiEngine()->getEiCommandCollection()->add($this->relationAjahEiCommand);
	}
	
	
	public function isFiltered(): bool {
		return $this->filtered;
	}
	
	public function setFiltered(bool $filtered) {
		$this->filtered = $filtered;
	}
	
	protected function setupEmbeddedEditEiCommand() {
		$this->embeddedEditEiCommand = new EmbeddedEditPseudoCommand('Edit embedded in ' 
						. $this->getRelationEiField()->getEiEngine()->getEiThing()->getLabelLstr() 
						. ' - ' . $this->getTargetEiMask()->getLabelLstr(), 
				$this->getRelationEiField()->getId(), $this->getTargetEiSpec()->getId());
		
		$this->relationEiField->getEiEngine()->getEiCommandCollection()
				->add($this->embeddedEditEiCommand);
	}
	
// 	public function hasRecursiveConflict(EiState $eiState) {
// 		$target = $this->getTarget();
// 		while (null !== ($eiState = $eiState->getParent())) {
// 			if ($eiState->getContextEiMask()->getEiEngine()->getEiSpec()->equals($target)) {
// 				return true;
// 			}
// 		}
// 		return false;
// 	}
	
	public function isReadOnly(EiMapping $mapping, EiState $eiState) {
		return $this->relationEiField->getStandardEditDefinition()->isReadOnly()
				|| ($this->isFiltered() && $eiState->getEiRelation($this->relationEiField->getId()));
	}
	
	public function getRelationEntityProperty(): RelationEntityProperty {
		return $this->relationEiField->getEntityProperty();
	}
	
	public function findTargetEiField() {
		if ($this->targetMasterEiField !== null) {
			return $this->targetMasterEiField;
		}
		
		$targetEiMask = $this->getTargetEiMask();
		$relationEntityProperty = $this->getRelationEntityProperty();
		foreach ($targetEiMask->getEiEngine()->getEiFieldCollection() as $targetEiField) {
			if (!($targetEiField instanceof RelationEiField)) continue;
			
			$targetRelationEntityProperty = $targetEiField->getEntityProperty();
			
			$targetRelation = $targetRelationEntityProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($relationEntityProperty)) {
				return $targetEiField;
			}
		}
	
		return null;
	}
	
	public function getTargetMasterEiField() {
		if ($this->targetMasterEiField === null && !$this->getRelationEntityProperty()->isMaster()) {
			throw new IllegalStateException(get_class($this->relationEiField) . ' not set up');
		}
		return $this->targetMasterEiField;
	}
	
// 	public function isMaster() {
// 		return $this->getRelationEntityProperty()->isMaster();
// 	}
		
	public function createTargetEiState(ManageState $manageState, EiState $eiState, EiSelection $eiSelection = null, 
			ControllerContext $targetControllerContext): EiState {
		$targetEiState = $manageState->createEiState($this->getTargetEiMask(), $targetControllerContext);
		$this->configureTargetEiState($targetEiState, $eiState, $eiSelection);
		
		return $targetEiState;
	}
	
	public function createTargetReadPseudoEiState(EiState $eiState, EiMapping $eiMapping = null): EiState {
		$targetEiState = $this->createTargetPseudoEiState($eiState, $eiMapping);
		
		$eiPermissionManager = $targetEiState->getManageState()->getEiPermissionManager();
		$targetEiState->setEiExecution($eiPermissionManager->createUnboundEiExceution(
				$this->getTargetEiMask(), new EiCommandPath(array()), $eiState->getN2nContext()));
		
		return $targetEiState;
	}
	
	public function createTargetEditPseudoEiState(EiState $eiState, EiMapping $eiMapping): EiState {
		$targetEiState = $this->createTargetPseudoEiState($eiState, $eiMapping);
		
		$eiPermissionManager = $targetEiState->getManageState()->getEiPermissionManager();
		$targetEiState->setEiExecution($eiPermissionManager->createEiExecution(
				$this->embeddedEditEiCommand, $eiState->getN2nContext()));
		
		return $targetEiState;
	}
	
	private function createTargetPseudoEiState(EiState $eiState, EiMapping $eiMapping = null): EiState {
	    $eiSelection = null;
	    if ($eiMapping !== null) {
	        $eiSelection = $eiMapping->getEiSelection();
	    }
	    
	    $targetCmdContextPath = $eiState->getControllerContext()->getCmdContextPath();
		if ($eiSelection === null || $eiSelection->isNew()) {
		    $targetCmdContextPath = $targetCmdContextPath->ext($this->relationEiCommand->getId(), 'rel');
		} else {
			$targetCmdContextPath = $targetCmdContextPath->ext($this->relationEiCommand->getId(), 'relentry', 
					$eiMapping->getIdRep());
		}
		
		$targetControllerContext = new ControllerContext(new Path(array()), $targetCmdContextPath);
		$targetEiStateFactory = new EiStateFactory($this->getTargetEiMask());
		$targetEiState = $targetEiStateFactory->create($targetControllerContext, $eiState->getManageState(), true, $eiState);
		
		$this->configureTargetEiState($targetEiState, $eiState, $eiSelection/*, $editCommandRequired*/);
		
		return $targetEiState;
	}
	
	public function applyEiExecution(EiState $targetEiState, bool $useEmbeddedEditEiCommand) {
		try {
			if ($useEmbeddedEditEiCommand) {
				
			} else {
				
			}
			return true;
		} catch (InaccessibleControlException $e) {
			return false;
		}
	}
	
	protected function configureTargetEiState(EiState $targetEiState, EiState $eiState, 
			EiSelection $eiSelection = null/*, $editCommandRequired = null*/) {
		if ($eiSelection === null) return $targetEiState;
				
		if (null !== ($targetCriteriaFactory = $this->createTargetCriteriaFactory($eiSelection))) {
			$targetEiState->setCriteriaFactory($targetCriteriaFactory);
		}
		
		$this->applyTargetModificators($targetEiState, $eiState, $eiSelection);
		
		return $targetEiState;
	}

	protected function createTargetCriteriaFactory(EiSelection $eiSelection) {
		if ($eiSelection->isNew()) return null;

		if (!$this->getRelationEntityProperty()->isMaster() && !$this->isSourceMany()) {
			return new MappedOneToCriteriaFactory($this->getRelationEntityProperty()->getRelation(), 
					$eiSelection->getLiveObject());
		}

		return new RelationCriteriaFactory($this->getRelationEntityProperty(), $eiSelection->getLiveObject());
	}
	
	protected function applyTargetModificators(EiState $targetEiState, EiState $eiState, 
			EiSelection $eiSelection) {
		$targetEiField = $this->findTargetEiField();
		
		if (null !== $targetEiField) {
			$targetEiModificatorCollection = $targetEiField->getEiEngine()->getEiModificatorCollection();
			
			$targetEiState->setEiRelation($targetEiField->getId(), new EiRelation($eiState, $eiSelection, 
					$this->relationEiField));
			
			if (!$eiSelection->isDraft()) {
				$targetEiModificatorCollection->add(new MappedRelationEiModificator($targetEiState, 
						RelationEntry::from($eiSelection), EiFieldPath::from($targetEiField), $this->isSourceMany()));
			}
		}
		
		if ($this->getRelationEntityProperty()->isMaster() && !$eiSelection->isDraft()) {
			$targetEiModificatorCollection = $this->targetEiMask->getEiEngine()->getEiModificatorCollection();
			$targetEiModificatorCollection->add(new MasterRelationEiModificator($targetEiState, $eiSelection->getLiveObject(),
					$this->relationEiField->getObjectPropertyAccessProxy(), $this->isTargetMany()));
		}
	}
	
	public function isPersistCascaded() {
		return $this->getRelationEntityProperty()->getRelation()->getCascadeType() & CascadeType::PERSIST;
	}
	
// 	public function isRemoveCascaded() {
// 		return $this->getRelationEntityProperty()->getRelation()->getCascadeType() & CascadeType::REMOVE;
// 	}
	
// 	public function isJoinTableRelation() {
// 		return $this->getRelationEntityProperty()->getRelation() instanceof JoinTableRelation;
// 	}

	public function buildTargetNewEntryFormUrl(EiMapping $eiMapping, bool $draft, EiState $eiState, HttpContext $httpContext): Url {
		$pathParts = array($this->relationEiCommand->getId());
		if ($eiMapping->isNew()) {
			$pathParts[] = 'relunknownentry';
		} else {
			$pathParts[] = 'relentry';
			$pathParts[] = $eiMapping->getIdRep();
		}
		$pathParts[] = $this->relationAjahEiCommand->getId();
		$contextUrl = $httpContext->getControllerContextPath($eiState->getControllerContext())->ext(...$pathParts)
				->toUrl();
		return RelationAjahController::buildNewFormUrl($contextUrl, $draft);
	}
}
