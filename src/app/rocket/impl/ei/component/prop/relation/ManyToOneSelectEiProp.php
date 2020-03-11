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
namespace rocket\impl\ei\component\prop\relation;

use n2n\util\type\ArgUtils;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\relation\model\filter\ToOneSecurityFilterProp;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\impl\ei\component\command\common\controller\GlobalOverviewJhtmlController;
use rocket\ei\util\Eiu;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\component\prop\FieldEiProp;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;

class ManyToOneSelectEiProp extends RelationEiPropAdapter implements FieldEiProp {
	
	function __construct() {
		parent::__construct();
		
		$this->setup(
				new DisplayConfig(ViewMode::all()),
				new RelationModel($this, true, false, RelationModel::MODE_SELECT, new EditConfig()));
	}
	
	function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_MANY_TO_ONE);
		
		parent::setEntityProperty($entityProperty);
	}
	
	function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkSelect($this, $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->getEditConfig()->isMandatory());
		return $field;
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->getEditConfig()->isReadOnly()) {
			return new RelationLinkGuiField($eiu, $this->getRelationModel());
		}
		
		return new ToOneGuiField($eiu, $this->getRelationModel());
	}
	
	
// 	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
// 		$eiPropRelation = $this->eiPropRelation;
// 		CastUtils::assertTrue($eiPropRelation instanceof SelectEiPropRelation);
		
// 		if (!$eiPropRelation->isHiddenIfTargetEmpty()) {
// 			return parent::buildDisplayDefinition($eiu);
// 		}

// 		$eiFrame = $eiu->frame()->getEiFrame();
// 		$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame);
		
// 		$targetEiu = new Eiu($targetReadEiFrame);
// 		$eiPropRelation = $this->eiPropRelation;
// 		CastUtils::assertTrue($eiPropRelation instanceof SelectEiPropRelation);
		
// 		if ($eiPropRelation->isHiddenIfTargetEmpty()
// 				&& 0 == $targetEiu->frame()->count(Boundry::NON_SECURITY_TYPES)) {
// 			return null;
// 		}
		
// 		return parent::buildDisplayDefinition($eiu);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
// 	 */
// 	public function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
// 		$mapping = $eiu->entry()->getEiEntry();
// 		$eiFrame = $eiu->frame()->getEiFrame();
// 		$relationEiField = $mapping->getEiField(EiPropPath::from($this));
// 		$targetReadEiFrame = null;
		
// 		try {
// 			$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
// 		} catch (InaccessibleEiCommandPathException $e) {
// 			return new InaccessibleGuiField($this->getLabelLstr());
// 		}
		
// 		$eiPropRelation = $this->eiPropRelation;
// 		CastUtils::assertTrue($eiPropRelation instanceof SelectEiPropRelation);
		
// 		$toOneEditable = null;
// 		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
// 			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
// 			$toOneEditable = new ToOneEditable($this->getLabelLstr(), $this->editConfig->isMandatory(),
// 					$relationEiField, $targetReadEiFrame, $targetEditEiFrame);
			
// 			$toOneEditable->setSelectOverviewToolsUrl($this->eiPropRelation->buildTargetOverviewToolsUrl(
// 					$eiFrame, $eiu->frame()->getHttpContext()));
			
// 			if ($this->eiPropRelation->isEmbeddedAddActivated($eiu->frame()->getEiFrame())
// 					 && $targetEditEiFrame->getEiExecution()->isGranted()) {
// 				$toOneEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEiuEntryFormUrl(
// 						$mapping, false, $eiFrame, $eiu->frame()->getHttpContext()));
// 			}
// 		}
		
// 		return new ManyToOneGuiField($this->getLabelLstr(), $relationEiField, $targetReadEiFrame, $toOneEditable);		
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\draft\DraftProperty::createDraftValueSelection()
// 	 */
// 	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
// 			N2nContext $n2nContext): DraftValueSelection {
				
// 		return new SimpleToOneDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)),
// 				$dm->getEntityManager(), $this->eiPropRelation->getTargetEiType()->getEntityModel());
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
// 	 */
// 	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
// 			PersistDraftAction $persistDraftAction) {
// 		ArgUtils::valObject($value, true);
		
// 		if ($value === null) {
// 			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), null);
// 			return;
// 		}
		
// 		$em = $persistDraftAction->getQueue()->getEntityManager();
// 		if (!$em->contains($value)) {
// 			$em->persist($value);
// 		}
		
// 		$target = $this->eiPropRelation->getTargetEiType();
		
// 		$targetId = $target->extractId($value);
// 		if ($targetId === null) {
// 			$em->flush();
// 			$targetId = $target->extractId($value);
// 		}
		
// 		$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), 
// 				$target->idToPid($targetId));
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
// 	 */
// 	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
// 			RemoveDraftAction $draftActionQueue) {
// 	}
	
// 	public function writeDraftValue($object, $value) {
// 		$this->getObjectPropertyAccessProxy()->setValue($object, $value);
// 	}
	
// 	public function buildFilterProp(Eiu $eiu): ?FilterProp  {
// 		$eiuFrame = $eiu->frame(false);
// 		if (null === $eiuFrame) return null;
		
// 		$eiFrame = $eiuFrame->getEiFrame();
// 		$filterProp = parent::buildManagedFilterProp($eiFrame);
// 		if ($filterProp === null) return null;
// 		CastUtils::assertTrue($filterProp instanceof RelationFilterProp);
		
// 		$that = $this;
// 		$filterProp->setTargetSelectUrlCallback(function (HttpContext $httpContext) use ($that, $eiFrame) {
// 			return $that->eiPropRelation->buildTargetOverviewToolsUrl($eiFrame, $httpContext);
// 		});
				
// 		return $filterProp;
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\impl\ei\component\prop\relation\SimpleRelationEiPropAdapter::buildFilterProp()
// 	 */
// 	public function buildFilterProp(Eiu $eiu): ?FilterProp {
// 		$filterProp = parent::buildFilterProp($eiu);
// 		CastUtils::assertTrue($filterProp instanceof RelationFilterProp);
		
// 		$n2nContext = $eiu->getN2nContext();
		
// 		$targetSelectToolsUrl = GlobalOverviewJhtmlController::buildToolsAjahUrl(
// 				$n2nContext->lookup(ScrRegistry::class), $this->eiPropRelation->getTargetEiType(),
// 				$this->eiPropRelation->getTargetEiMask());
		
// 		$that = $this;
// 		$filterProp->setTargetSelectUrlCallback(function () use ($n2nContext, $that) {
// 			return GlobalOverviewJhtmlController::buildToolsAjahUrl(
// 					$n2nContext->lookup(ScrRegistry::class), $that->eiPropRelation->getTargetEiType(),
// 					$that->eiPropRelation->getTargetEiMask());
// 		});
			
// 		return $filterProp;
// 	}
	
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp {
		$eiEntryFilterProp = parent::createSecurityFilterProp($n2nContext);
		CastUtils::assertTrue($eiEntryFilterProp instanceof ToOneSecurityFilterProp);
				
		$that = $this;
		$eiEntryFilterProp->setTargetSelectToolsUrlCallback(function () use ($n2nContext, $that) {
			return GlobalOverviewJhtmlController::buildToolsAjahUrl(
					$n2nContext->lookup(ScrRegistry::class), $this->eiPropRelation->getTargetEiType(),
					$this->eiPropRelation->getTargetEiMask());
		});
				
		return $eiEntryFilterProp;
	}
	

}

// class SimpleToOneDraftValueSelection extends SimpleDraftValueSelection {
// 	private $em;
// 	private $targetEntityModel;
	
// 	public function __construct($columnAlias, EntityManager $em, EntityModel $targetEntityModel) {
// 		parent::__construct($columnAlias);
// 		$this->em = $em;
// 		$this->targetEntityModel = $targetEntityModel;
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\draft\DraftValueSelection::buildDraftValue()
// 	 */
// 	public function buildDraftValue() {
// 		if ($this->rawValue === null) return null;
		
// 		$targetId = $this->targetEntityModel->getIdDef()->getEntityProperty()->parseValue($this->rawValue, 
// 				$this->em->getPdo());
// 		return $this->em->find($this->targetEntityModel->getClass(), $targetId);
// 	}
// }
