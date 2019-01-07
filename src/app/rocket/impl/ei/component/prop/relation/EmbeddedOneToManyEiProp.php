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

use rocket\impl\ei\component\prop\relation\model\relation\EmbeddedEiPropRelation;
use rocket\impl\ei\component\prop\relation\conf\RelationEiPropConfigurator;
use rocket\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\impl\ei\component\prop\relation\model\ToManyEditable;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\impl\ei\component\prop\relation\model\EmbeddedOneToManyGuiField;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\ei\manage\draft\RemoveDraftAction;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\manage\draft\PersistDraftAction;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\EiPropPath;
use rocket\ei\manage\DraftEiObject;
use rocket\ei\manage\LiveEiObject;
use n2n\util\type\CastUtils;
use rocket\ei\util\Eiu;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ArgUtils;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\persistence\PdoStatement;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use n2n\persistence\meta\data\JoinType;
use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use rocket\ei\manage\draft\DraftFetcher;
use rocket\ei\manage\draft\DraftActionAdapter;
use n2n\persistence\Pdo;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\draft\stmt\DraftStmtBuilder;
use rocket\ei\manage\draft\ModDraftAction;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;

class EmbeddedOneToManyEiProp extends ToManyEiPropAdapter /*implements DraftableEiProp, Draftable*/ {
	private $targetOrderEiPropPath;
	private $reduced = true;
	private $orphansAllowed = false;
	
	public function __construct() {	
		parent::__construct();
		
		$this->initialize(new EmbeddedEiPropRelation($this, false, true));
	}
	
	public function getOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	public function setOrphansAllowed(bool $orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);
	
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return boolean
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param bool $reduced
	 */
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}
		
	public function copy(Eiu $eiu, $value, Eiu $copyEiu) {
		$targetEiuFrame = (new Eiu($this->eiPropRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiEntry())))->frame();
		
		$newValue = array();
		foreach ($value as $key => $targetRelationEntry) {
			$newValue[$key] = RelationEntry::fromM($targetEiuFrame->copyEntry(
					$targetRelationEntry->toEiEntry($targetEiuFrame))->getEiEntry());
		}
		return $newValue;
	}
	
	
	
	public function setTargetOrderEiPropPath(EiPropPath $targetOrderEiPropPath = null) {
		$this->targetOrderEiPropPath = $targetOrderEiPropPath;
	}
	
	public function getTargetOrderEiPropPath() {
		return $this->targetOrderEiPropPath;
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = new RelationEiPropConfigurator($this);
		$eiPropConfigurator->setDisplayInOverviewDefault(false);
		return $eiPropConfigurator;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\Readable::read()
	 */
	public function read(Eiu $eiu) {
		$targetEiObjects = [];
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDrafts = $eiu->entry()->readNativValue($this);
			if ($targetDrafts === null) return $targetEiObjects;
			
			foreach ($targetDrafts as $targetDraft) {
				$targetEiObjects[] = new DraftEiObject($targetDraft);
			}
			return $targetEiObjects; 
		}
	
		$targetEntityObjs = $eiu->entry()->readNativValue($this);
		if ($targetEntityObjs === null) return $targetEiObjects;
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			$targetEiObjects[] = LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetEntityObj);
		}
		return $targetEiObjects;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\Writable::write()
	 */
	public function write(Eiu $eiu, $value) {
		CastUtils::assertTrue(is_array($value));
	
		if ($eiu->object()->isDraftProp($this)) {
			$targetDrafts = array();
			foreach ($value as $targetEiObject) {
				CastUtils::assertTrue($targetEiObject instanceof EiObject);
				$targetDrafts[] = $targetEiObject->getDraft();
			}
			$eiu->entry()->writeNativeValue($this, $targetDrafts);
			return;
		}
	
		$targetEntityObjs = new \ArrayObject();
		foreach ($value as $targetEiObject) {
			CastUtils::assertTrue($targetEiObject instanceof EiObject);
			$targetEntityObjs[] = $targetEiObject->getEiEntityObj()->getEntityObj();
		}
		$eiu->entry()->writeNativeValue($this, $targetEntityObjs);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 * @return GuiField
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		$eiEntry = $eiu->entry()->getEiEntry();
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationEiField = $eiEntry->getEiField(EiPropPath::from($this));
		
		try {
			$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $eiEntry);
		} catch (InaccessibleEiCommandPathException $e) {
			return new InaccessibleGuiField($this->getLabelLstr());
		}
		
		$toManyEditable = null;
		if (!$this->eiPropRelation->isReadOnly($eiEntry, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $eiEntry);
			
			$toManyEditable = new ToManyEditable($this->getLabelLstr(), $relationEiField, $targetReadEiFrame,
					$targetEditEiFrame, $this->getRealMin(), $this->getMax());
			$toManyEditable->setReduced($this->reduced);
			$draftMode = $eiEntry->getEiObject()->isDraft();
			$toManyEditable->setDraftMode($draftMode);
			
			if ($targetEditEiFrame->getEiExecution()->isGranted()) {
				$toManyEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEiuEntryFormUrl(
						$eiEntry, $draftMode, $eiFrame, $eiu->frame()->getHttpContext()));
			}
			$toManyEditable->setTargetOrderEiPropPath($this->targetOrderEiPropPath);
		}
		
		return new EmbeddedOneToManyGuiField($this->getLabelLstr(), $this->isReduced(), $relationEiField, 
				$targetReadEiFrame, $eiu->gui()->isCompact(), $toManyEditable);
	}
	
// 	const T_ALIAS = 't';
// 	const JT_ALIAS = 'jt';
	const JT_DRAFT_ID_COLUMN = 'draft_id';
	const JT_TARGET_DRAFT_ID_COLUMN = 'target_draft_id';
	const JT_ORDER_INDEX = 'order_index';
	
	private function getJoinTableName(DraftStmtBuilder $draftStmtBuilder) {
		return DraftMetaInfo::buildFieldTableName($draftStmtBuilder->getTableName(), EiPropPath::from($this));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $fetchDraftStmtBuilder, DraftManager $dm,
			N2nContext $n2nContext): DraftValueSelection {
				
		$joinTableName = $this->getJoinTableName($fetchDraftStmtBuilder);
		
		$targetDraftDefinition = $this->getEiPropRelation()->getTargetEiMask()->getEiEngine()->getDraftDefinition();
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
				$this->getEiPropRelation()->getTargetEiType(), $targetDraftDefinition);
		
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
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($targetDrafts, $oldTargetDrafts,
			PersistDraftStmtBuilder $persistDraftStmtBuilder, PersistDraftAction $persistDraftAction) {
		$relationDraftAction = $this->createRelationDraftAction($persistDraftStmtBuilder, $persistDraftAction);
		
		$draftActionQueue = $persistDraftAction->getQueue();
		$targetDraftDefinition = $this->getEiPropRelation()->getTargetEiMask()->getEiEngine()->getDraftDefinition();
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
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
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
	 * @see \rocket\ei\manage\draft\DraftValueSelection::bind()
	 */
	public function bind(PdoStatement $stmt) {
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftValueSelection::buildDraftValue()
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
		$deleteBuilder->getWhereComparator()->match(new QueryColumn(EmbeddedOneToManyEiProp::JT_DRAFT_ID_COLUMN),
				'=', new QueryPlaceMarker());
		$deleteStmt = $this->pdo->prepare($deleteBuilder->toSqlString());
		$deleteStmt->execute(array($this->getDraftId()));
		
		if (empty($this->targetDraftDefs)) return;
		
		$insertBuilder = $metaData->createInsertStatementBuilder();
		$insertBuilder->setTable($this->joinTableName);
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiProp::JT_DRAFT_ID_COLUMN), new QueryPlaceMarker());
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiProp::JT_TARGET_DRAFT_ID_COLUMN), new QueryPlaceMarker());
		$insertBuilder->addColumn(new QueryColumn(EmbeddedOneToManyEiProp::JT_ORDER_INDEX), new QueryPlaceMarker());
		
		$insertStmt = $this->pdo->prepare($insertBuilder->toSqlString());
		foreach ($this->targetDraftDefs as $draftDef) {
			$insertStmt->execute(array($this->draftId, $draftDef['targetDraftId'], $draftDef['orderIndex']));
		}
	}
}