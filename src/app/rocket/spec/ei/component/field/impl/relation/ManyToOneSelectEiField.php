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
namespace rocket\spec\ei\component\field\impl\relation;

use rocket\spec\ei\component\field\impl\relation\model\relation\SelectEiFieldRelation;

use rocket\spec\ei\component\field\impl\relation\model\RelationMappable;
use rocket\spec\ei\manage\EiState;
use n2n\impl\persistence\orm\property\relation\Relation;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use rocket\spec\ei\component\field\impl\adapter\StandardEditDefinition;
use rocket\spec\ei\component\field\impl\relation\model\ManyToOneGuiElement;
use rocket\spec\ei\manage\draft\DraftProperty;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\DraftActionQueue;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\relation\model\ToOneEditable;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\draft\SimpleDraftValueSelection;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ArgUtils;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\component\field\impl\relation\model\filter\ToOneEiMappingFilterField;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\command\impl\common\controller\GlobalOverviewAjahController;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use rocket\spec\ei\component\field\impl\relation\model\filter\RelationFilterField;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\web\http\HttpContext;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;

class ManyToOneSelectEiField extends ToOneEiFieldAdapter {

	public function __construct() {
		parent::__construct();
		
		$this->initialize(new SelectEiFieldRelation($this, true, false));
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_MANY_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		$targetEntityObj = null;
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetEntityObj = $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this));
		} else {
			$targetEntityObj = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		}
		
		if ($targetEntityObj === null) return null;
		
		return LiveEiSelection::create($this->eiFieldRelation->getTargetEiSpec(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiSelection);
	
		$targetEntityObj = null;
		if ($value !== null) {
			$targetEntityObj = $value->getLiveEntry()->getEntityObj();
		}
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$eiObject->getDraftValueMap()->setValue(EiFieldPath::from($this), $targetEntityObj);
		} else {
			$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObj);
		}		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 */
	public function buildGuiElement(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiMapping();
		$eiState = $eiu->frame()->getEiState();
		$relationMappable = $mapping->getMappingProfile()->getMappable(EiFieldPath::from($this));
		$targetReadEiState = $this->eiFieldRelation->createTargetReadPseudoEiState($eiState, $mapping);
		
		$toOneEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($mapping, $eiState)) {
			$targetEditEiState = $this->eiFieldRelation->createTargetEditPseudoEiState($eiState, $mapping);
			$toOneEditable = new ToOneEditable($this->getLabelLstr(), $this->standardEditDefinition->isMandatory(),
					$relationMappable, $targetReadEiState, $targetEditEiState);
			
			$toOneEditable->setSelectOverviewToolsUrl($this->eiFieldRelation->buildTargetOverviewToolsUrl(
					$eiState, $eiu->getHttpContext()));
			
			if ($this->eiFieldRelation->isEmbeddedAddActivated($eiu->frame()->getEiState())
					 && $targetEditEiState->getEiExecution()->isGranted()) {
				$toOneEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl(
						$mapping, false, $eiState, $eiu->getRequest()));
			}
		}
		
		return new ManyToOneGuiElement($this->getLabelLstr(), $relationMappable, $targetReadEiState, $toOneEditable);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
				
		return new SimpleToOneDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiFieldPath::from($this)),
				$dm->getEntityManager(), $this->eiFieldRelation->getTargetEiSpec()->getEntityModel());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
			PersistDraftAction $persistDraftAction) {
		ArgUtils::valObject($value, true);
		
		if ($value === null) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiFieldPath::from($this), null);
			return;
		}
		
		$em = $persistDraftAction->getQueue()->getEntityManager();
		if (!$em->contains($value)) {
			$em->persist($value);
		}
		
		$target = $this->eiFieldRelation->getTargetEiSpec();
		
		$targetId = $target->extractId($value);
		if ($targetId === null) {
			$em->flush();
			$targetId = $target->extractId($value);
		}
		
		$persistDraftStmtBuilder->registerColumnRawValue(EiFieldPath::from($this), 
				$target->idToIdRep($targetId));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
	 */
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $draftActionQueue) {
	}
	
	public function writeDraftValue($object, $value) {
		$this->getObjectPropertyAccessProxy()->setValue($object, $value);
	}
	
	public function buildManagedFilterField(EiState $eiState) {
		$filterField = parent::buildManagedFilterField($eiState);
		CastUtils::assertTrue($filterField instanceof RelationFilterField);
		
		$that = $this;
		$filterField->setTargetSelectUrlCallback(function (HttpContext $httpContext) use($that, $eiState) {
			return $that->eiFieldRelation->buildTargetOverviewToolsUrl($eiState, $httpContext);
		});
				
		return $filterField;
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		$filterField = parent::buildFilterField($n2nContext);
		CastUtils::assertTrue($filterField instanceof RelationFilterField);
		
		$targetSelectToolsUrl = GlobalOverviewAjahController::buildToolsAjahUrl(
				$n2nContext->lookup(ScrRegistry::class), $this->eiFieldRelation->getTargetEiSpec(),
				$this->eiFieldRelation->getTargetEiMask());
		
		$that = $this;
		$filterField->setTargetSelectUrlCallback(function () use ($n2nContext, $that) {
			return GlobalOverviewAjahController::buildToolsAjahUrl(
					$n2nContext->lookup(ScrRegistry::class), $that->eiFieldRelation->getTargetEiSpec(),
					$that->eiFieldRelation->getTargetEiMask());
		});
			
		return $filterField;
	}
	
	public function createEiMappingFilterField(N2nContext $n2nContext): EiMappingFilterField {
		$eiMappingFilterField = parent::createEiMappingFilterField($n2nContext);
		CastUtils::assertTrue($eiMappingFilterField instanceof ToOneEiMappingFilterField);
				
		$that = $this;
		$eiMappingFilterField->setTargetSelectToolsUrlCallback(function () use ($n2nContext, $that) {
			return GlobalOverviewAjahController::buildToolsAjahUrl(
					$n2nContext->lookup(ScrRegistry::class), $this->eiFieldRelation->getTargetEiSpec(),
					$this->eiFieldRelation->getTargetEiMask());
		});
				
		return $eiMappingFilterField;
	}
}

class SimpleToOneDraftValueSelection extends SimpleDraftValueSelection {
	private $em;
	private $targetEntityModel;
	
	public function __construct($columnAlias, EntityManager $em, EntityModel $targetEntityModel) {
		parent::__construct($columnAlias);
		$this->em = $em;
		$this->targetEntityModel = $targetEntityModel;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		if ($this->rawValue === null) return null;
		
		$targetId = $this->targetEntityModel->getIdDef()->getEntityProperty()->parseValue($this->rawValue, 
				$this->em->getPdo());
		return $this->em->find($this->targetEntityModel->getClass(), $targetId);
	}
}
