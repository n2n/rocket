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

use rocket\spec\ei\component\field\impl\relation\model\relation\EmbeddedEiFieldRelation;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\component\field\impl\relation\conf\RelationEiFieldConfigurator;
use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\component\field\impl\relation\model\ToManyEditable;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\relation\model\EmbeddedOneToManyGuiElement;
use rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\manage\gui\GuiElement;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\field\impl\relation\model\RelationMappable;
use rocket\spec\ei\manage\DraftEiSelection;
use rocket\spec\ei\manage\LiveEiSelection;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\persistence\PdoStatement;
use rocket\spec\ei\manage\draft\stmt\DraftMetaInfo;
use n2n\persistence\meta\data\JoinType;
use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use rocket\spec\ei\manage\draft\DraftFetcher;
use rocket\spec\ei\manage\draft\DraftActionQueue;
use rocket\spec\ei\manage\draft\DraftActionAdapter;
use n2n\persistence\Pdo;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\draft\stmt\DraftStmtBuilder;
use rocket\spec\ei\manage\draft\ModDraftAction;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;

class EmbeddedOneToManyEiField extends ToManyEiFieldAdapter /*implements DraftableEiField, Draftable*/ {
	private $targetOrderEiFieldPath;
	private $orphansAllowed = false;
	
	public function __construct() {	
		parent::__construct();
		
		$this->initialize(new EmbeddedEiFieldRelation($this, false, true));
	}
	
	public function getOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	public function setOrphansAllowed(bool $orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);
	
		parent::setEntityProperty($entityProperty);
	}
		
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		$targetEiuFrame = new EiuFrame($this->eiFieldRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiMapping()));
		
		$newValue = array();
		foreach ($value as $key => $targetRelationEntry) {
			$newValue[$key] = RelationEntry::fromM($targetEiuFrame->createEiMappingCopy(
					$targetRelationEntry->toEiMapping($targetEiuFrame)));
		}
		return $newValue;
	}
	
	
	
	public function setTargetOrderEiFieldPath(EiFieldPath $targetOrderEiFieldPath = null) {
		$this->targetOrderEiFieldPath = $targetOrderEiFieldPath;
	}
	
	public function getTargetOrderEiFieldPath() {
		return $this->targetOrderEiFieldPath;
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new RelationEiFieldConfigurator($this);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		$targetEiSelections = array();
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDrafts = $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this));
			if ($targetDrafts === null) return $targetEiSelections;
			
			foreach ($targetDrafts as $targetDraft) {
				$targetEiSelections[] = new DraftEiSelection($targetDraft);
			}
			return $targetEiSelections; 
		}
	
		$targetEntityObjs = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		if ($targetEntityObjs === null) return $targetEiSelections;
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			$targetEiSelections[] = LiveEiSelection::create($this->eiFieldRelation->getTargetEiSpec(), $targetEntityObj);
		}
		return $targetEiSelections;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue(is_array($value));
	
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDrafts = array();
			foreach ($value as $targetEiSelection) {
				CastUtils::assertTrue($targetEiSelection instanceof EiSelection);
				$targetDrafts[] = $targetEiSelection->getDraft();
			}
			$eiObject->getDraftValueMap()->setValue(EiFieldPath::from($this), $targetDrafts);
			return;
		}
	
		$targetEntityObjs = new \ArrayObject();
		foreach ($value as $targetEiSelection) {
			CastUtils::assertTrue($targetEiSelection instanceof EiSelection);
			$targetEntityObjs[] = $targetEiSelection->getLiveEntry()->getEntityObj();
		}
		$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObjs);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 * @return GuiElement
	 */
	public function buildGuiElement(Eiu $eiu) {
		$eiMapping = $eiu->entry()->getEiMapping();
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationMappable = $eiMapping->getMappable(EiFieldPath::from($this));
		$targetReadEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame, $eiMapping);
		
		$toManyEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($eiMapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $eiMapping);
			
			$toManyEditable = new ToManyEditable($this->getLabelLstr(), $relationMappable, $targetReadEiFrame,
					$targetEditEiFrame, $this->getRealMin(), $this->getMax());
				
			$draftMode = $eiMapping->getEiSelection()->isDraft();
			$toManyEditable->setDraftMode($draftMode);
			
			if ($targetEditEiFrame->getEiExecution()->isGranted()) {
				$toManyEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl(
						$eiMapping, $draftMode, $eiFrame, $eiu->frame()->getHttpContext()));
			}
			$toManyEditable->setTargetOrderEiFieldPath($this->targetOrderEiFieldPath);
		}
		
		return new EmbeddedOneToManyGuiElement($this->getLabelLstr(), $relationMappable, $targetReadEiFrame, $toManyEditable);
	}
	
// 	const T_ALIAS = 't';
// 	const JT_ALIAS = 'jt';
	const JT_DRAFT_ID_COLUMN = 'draft_id';
	const JT_TARGET_DRAFT_ID_COLUMN = 'target_draft_id';
	const JT_ORDER_INDEX = 'order_index';
	
	private function getJoinTableName(DraftStmtBuilder $draftStmtBuilder) {
		return DraftMetaInfo::buildFieldTableName($draftStmtBuilder->getTableName(), EiFieldPath::from($this));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $fetchDraftStmtBuilder, DraftManager $dm,
			N2nContext $n2nContext): DraftValueSelection {
				
		$joinTableName = $this->getJoinTableName($fetchDraftStmtBuilder);
		
		$targetDraftDefinition = $this->getEiFieldRelation()->getTargetEiMask()->getEiEngine()->getDraftDefinition();
		$targetFetchDraftStmtBuilder = $targetDraftDefinition->createFetchDraftStmtBuilder($dm, $n2nContext, 't');
		$targetStmtBuilder = $targetFetchDraftStmtBuilder->getSelectStatementBuilder();
		
		$phName = $targetFetchDraftStmtBuilder->createPlaceholderName();
		$targetStmtBuilder->addJoin(JoinType::INNER, new QueryTable($joinTableName), 'jt')
				->match(new QueryColumn(DraftMetaInfo::COLUMN_ID, 't'), '=', 
						new QueryColumn(self::JT_TARGET_DRAFT_ID_COLUMN, 'jt'));
				
		$targetStmtBuilder->getWhereComparator()->match(new QueryColumn(self::JT_DRAFT_ID_COLUMN, 'jt'), 
				'=', new QueryPlaceMarker($phName));
		$targetStmtBuilder->addOrderBy(new QueryColumn(self::JT_ORDER_INDEX, 'jt'), 'ASC');
		
		$targetDraftFetcher = $dm->createDraftFetcher($targetFetchDraftStmtBuilder, 
				$this->getEiFieldRelation()->getTargetEiSpec(), $targetDraftDefinition);
		
		return new EmbeddedToManyDraftValueSelection($fetchDraftStmtBuilder, $targetDraftFetcher, $phName);
	}
	
	private function createRelationDraftAction(DraftStmtBuilder $draftStmtBuilder, ModDraftAction $modDraftAction) {
		$relationDraftAction = new EmbeddedToManyDraftAction($draftStmtBuilder->getPdo(), 
				$this->getJoinTableName($draftStmtBuilder));
		
		if (!$modDraftAction->getDraft()->isNew()) {
		    $relationDraftAction->setDraftId($modDraftAction->getDraft()->getId());
		} else  {
		    $relationDraftAction->addDependent($modDraftAction);
		    $modDraftAction->executeAtEnd(function () use ($modDraftAction, $relationDraftAction) {
		        $relationDraftAction->setDraftId($modDraftAction->getDraft()->getId());
		    });
		}
		
		$modDraftAction->getQueue()->addDraftAction($relationDraftAction);
// 		$modDraftAction->executeWhenDisabled(function () use ($relationDraftAction) {
// 		    $relationDraftAction->disable();
// 		});

		return $relationDraftAction;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($targetDrafts, $oldTargetDrafts,
			PersistDraftStmtBuilder $persistDraftStmtBuilder, PersistDraftAction $persistDraftAction) {
		$relationDraftAction = $this->createRelationDraftAction($persistDraftStmtBuilder, $persistDraftAction);
		
		$draftActionQueue = $persistDraftAction->getQueue();
		$targetDraftDefinition = $this->getEiFieldRelation()->getTargetEiMask()->getEiEngine()->getDraftDefinition();
		$orderIndex = 0;
		$targetDraftIds = array();
		foreach ((array) $targetDrafts as $targetDraft) {
			$targetAction = $draftActionQueue->persist($targetDraft, $targetDraftDefinition);
			if (!$targetDraft->isNew()) {
				$targetDraftId = $targetDraft->getId();
				$targetDraftIds[$targetDraftId] = $targetDraftId;
				$relationDraftAction->addTargetDraftId($targetDraft->getId(), $orderIndex++);
				continue;
			}
			
			$relationDraftAction->addDependent($targetAction);
			$targetAction->executeAtEnd(function () use ($relationDraftAction, $targetDraft, $orderIndex) {
				$relationDraftAction->addTargetDraftId($targetDraft->getId(), $orderIndex);
			});
			$orderIndex++;
		}
		
		foreach ((array) $oldTargetDrafts as $oldTargetDrafts) {
			if (!isset($targetDraftIds[$oldTargetDrafts->getId()])) {
				$persistDraftAction->getQueue()->remove($oldTargetDrafts, true);
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
	 */
	public function supplyRemoveDraftStmtBuilder($targetDrafts, $oldTargetDrafts, 
			RemoveDraftStmtBuilder $removeDraftStmtBuilder, RemoveDraftAction $removeDraftAction) {
		$draftActionQueue = $removeDraftAction->getQueue();
		foreach ($oldTargetDrafts as $oldTargetDraft) {
			$draftActionQueue->remove($oldTargetDraft);
		}
		
		$this->createRelationDraftAction($removeDraftStmtBuilder, $removeDraftAction);
	}
	
	public function writeDraftValue($object, $value) {
		throw new NotYetImplementedException();
	}
}

class EmbeddedToManyDraftValueSelection implements DraftValueSelection {
	private $fetchDraftStmtBuilder;
	private $targetDraftFetcher;
	private $phName;
	
	public function __construct(FetchDraftStmtBuilder $fetchDraftStmtBuilder, DraftFetcher $targetDraftFetcher, 
			string $phName) {
		$this->fetchDraftStmtBuilder = $fetchDraftStmtBuilder;
		$this->targetDraftFetcher = $targetDraftFetcher;
		$this->phName = $phName;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftValueSelection::bind()
	 */
	public function bind(PdoStatement $stmt) {
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		$this->targetDraftFetcher->getFetchDraftStmtBuilder()->bindValue(
				$this->phName, $this->fetchDraftStmtBuilder->getBoundIdRawValue());
		
		return $this->targetDraftFetcher->fetch();
	}
}

class EmbeddedToManyDraftAction extends DraftActionAdapter {
	private $pdo;
	private $joinTableName;
	private $draftId;
	private $targetDraftDefs = array();
	
	public function __construct(Pdo $pdo, string $joinTableName) {
		$this->pdo = $pdo;
		$this->joinTableName = $joinTableName;
	}
	
	public function setDraftId(int $draftId) {
		$this->draftId = $draftId;
	}
	
	public function getDraftId() {
		if ($this->draftId !== null) {
			return $this->draftId;
		}
		
		throw new IllegalStateException();
	}
	
	public function addTargetDraftId(int $targetDraftId, int $orderIndex) {
		$this->targetDraftDefs[] = array('targetDraftId' => $targetDraftId, 'orderIndex' => $orderIndex);
	}
	
	protected function exec() {
		$metaData = $this->pdo->getMetaData();
		
		$deleteBuilder = $metaData->createDeleteStatementBuilder();
		$deleteBuilder->setTable($this->joinTableName);
		$deleteBuilder->getWhereComparator()->match(new QueryColumn(EmbeddedOneToManyEiField::JT_DRAFT_ID_COLUMN),
				'=', new QueryPlaceMarker());
		$deleteStmt = $this->pdo->prepare($deleteBuilder->toSqlString());
		$deleteStmt->execute(array($this->getDraftId()));
		
		if (empty($this->targetDraftDefs)) return;
		
		$insertBuilder = $metaData->createInsertStatementBuilder();
		$insertBuilder->setTable($this->joinTableName);
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiField::JT_DRAFT_ID_COLUMN), new QueryPlaceMarker());
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiField::JT_TARGET_DRAFT_ID_COLUMN), new QueryPlaceMarker());
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiField::JT_ORDER_INDEX), new QueryPlaceMarker());
		
		$insertStmt = $this->pdo->prepare($insertBuilder->toSqlString());
		foreach ($this->targetDraftDefs as $draftDef) {
			$insertStmt->execute(array($this->draftId, $draftDef['targetDraftId'], $draftDef['orderIndex']));
		}
	}
}
