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

use rocket\spec\ei\component\field\impl\relation\model\relation\SelectEiPropRelation;

use rocket\spec\ei\component\field\impl\relation\model\RelationEiField;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\persistence\orm\property\relation\Relation;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiPropRelation;
use rocket\spec\ei\component\field\impl\adapter\StandardEditDefinition;
use rocket\spec\ei\component\field\impl\relation\model\ManyToOneGuiField;
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
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\component\field\impl\relation\model\filter\ToOneEiMappingFilterField;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\component\command\impl\common\controller\GlobalOverviewAjahController;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use rocket\spec\ei\component\field\impl\relation\model\filter\RelationFilterField;
use rocket\spec\ei\manage\LiveEiObject;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\web\http\HttpContext;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;

class ManyToOneSelectEiProp extends ToOneEiPropAdapter {

	public function __construct() {
		parent::__construct();
		
		$this->initialize(new SelectEiPropRelation($this, true, false));
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
			$targetEntityObj = $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
		} else {
			$targetEntityObj = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		}
		
		if ($targetEntityObj === null) return null;
		
		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiObject);
	
		$targetEntityObj = null;
		if ($value !== null) {
			$targetEntityObj = $value->getEiEntityObj()->getEntityObj();
		}
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$eiObject->getDraftValueMap()->setValue(EiPropPath::from($this), $targetEntityObj);
		} else {
			$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObj);
		}		
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiMapping();
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationEiField = $mapping->getEiField(EiPropPath::from($this));
		$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		
		$toOneEditable = null;
		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
			$toOneEditable = new ToOneEditable($this->getLabelLstr(), $this->standardEditDefinition->isMandatory(),
					$relationEiField, $targetReadEiFrame, $targetEditEiFrame);
			
			$toOneEditable->setSelectOverviewToolsUrl($this->eiPropRelation->buildTargetOverviewToolsUrl(
					$eiFrame, $eiu->frame()->getHttpContext()));
			
			if ($this->eiPropRelation->isEmbeddedAddActivated($eiu->frame()->getEiFrame())
					 && $targetEditEiFrame->getEiExecution()->isGranted()) {
				$toOneEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEntryFormUrl(
						$mapping, false, $eiFrame, $eiu->getRequest()));
			}
		}
		
		return new ManyToOneGuiField($this->getLabelLstr(), $relationEiField, $targetReadEiFrame, $toOneEditable);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
				
		return new SimpleToOneDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)),
				$dm->getEntityManager(), $this->eiPropRelation->getTargetEiType()->getEntityModel());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
			PersistDraftAction $persistDraftAction) {
		ArgUtils::valObject($value, true);
		
		if ($value === null) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), null);
			return;
		}
		
		$em = $persistDraftAction->getQueue()->getEntityManager();
		if (!$em->contains($value)) {
			$em->persist($value);
		}
		
		$target = $this->eiPropRelation->getTargetEiType();
		
		$targetId = $target->extractId($value);
		if ($targetId === null) {
			$em->flush();
			$targetId = $target->extractId($value);
		}
		
		$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), 
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
	
	public function buildManagedFilterField(EiFrame $eiFrame) {
		$filterField = parent::buildManagedFilterField($eiFrame);
		CastUtils::assertTrue($filterField instanceof RelationFilterField);
		
		$that = $this;
		$filterField->setTargetSelectUrlCallback(function (HttpContext $httpContext) use($that, $eiFrame) {
			return $that->eiPropRelation->buildTargetOverviewToolsUrl($eiFrame, $httpContext);
		});
				
		return $filterField;
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		$filterField = parent::buildFilterField($n2nContext);
		CastUtils::assertTrue($filterField instanceof RelationFilterField);
		
		$targetSelectToolsUrl = GlobalOverviewAjahController::buildToolsAjahUrl(
				$n2nContext->lookup(ScrRegistry::class), $this->eiPropRelation->getTargetEiType(),
				$this->eiPropRelation->getTargetEiMask());
		
		$that = $this;
		$filterField->setTargetSelectUrlCallback(function () use ($n2nContext, $that) {
			return GlobalOverviewAjahController::buildToolsAjahUrl(
					$n2nContext->lookup(ScrRegistry::class), $that->eiPropRelation->getTargetEiType(),
					$that->eiPropRelation->getTargetEiMask());
		});
			
		return $filterField;
	}
	
	public function createEiMappingFilterField(N2nContext $n2nContext): EiMappingFilterField {
		$eiMappingFilterField = parent::createEiMappingFilterField($n2nContext);
		CastUtils::assertTrue($eiMappingFilterField instanceof ToOneEiMappingFilterField);
				
		$that = $this;
		$eiMappingFilterField->setTargetSelectToolsUrlCallback(function () use ($n2nContext, $that) {
			return GlobalOverviewAjahController::buildToolsAjahUrl(
					$n2nContext->lookup(ScrRegistry::class), $this->eiPropRelation->getTargetEiType(),
					$this->eiPropRelation->getTargetEiMask());
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
