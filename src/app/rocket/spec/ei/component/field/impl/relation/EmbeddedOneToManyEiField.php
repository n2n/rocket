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
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
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
use rocket\spec\ei\manage\gui\FieldSourceInfo;
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

class EmbeddedOneToManyEiField extends ToManyEiFieldAdapter /*implements DraftableEiField, Draftable*/ {
	
	public function __construct() {	
		parent::__construct();
		
		$this->initialize(new EmbeddedEiFieldRelation($this, false, true));
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);
	
		parent::setEntityProperty($entityProperty);
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
	public function buildGuiElement(FieldSourceInfo $entrySourceInfo) {
		$eiMapping = $entrySourceInfo->getEiMapping();
	
		$eiState = $entrySourceInfo->getEiState();
		$relationMappable = $eiMapping->getMappingProfile()->getMappable(EiFieldPath::from($this));
		$targetReadEiState = $this->eiFieldRelation->createTargetReadPseudoEiState($eiState, $eiMapping);
		
		$toManyEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($eiMapping, $eiState)) {
			$targetEditEiState = $this->eiFieldRelation->createTargetEditPseudoEiState($eiState, $eiMapping);
			
			$toManyEditable = new ToManyEditable($this->getLabelLstr(), $relationMappable, $targetReadEiState,
					$targetEditEiState, $this->getRealMin(), $this->getMax());
				
			$draftMode = $eiMapping->getEiSelection()->isDraft();
			$toManyEditable->setDraftMode($draftMode);
			
			if ($targetEditEiState->getEiExecution()->isGranted()) {
				$toManyEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl(
						$eiMapping, $draftMode, $eiState, $entrySourceInfo->getHttpContext()));
			}
		}
		
		return new EmbeddedOneToManyGuiElement($this->getLabelLstr(), $relationMappable, $targetReadEiState, $toManyEditable);
	}
	
// 	const T_ALIAS = 't';
// 	const JT_ALIAS = 'jt';
	const JT_DRAFT_ID_COLUMN = 'draft_id';
	const JT_TARGET_DRAFT_ID_COLUMN = 'target_draft_id';
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $fetchDraftStmtBuilder, DraftManager $dm,
			N2nContext $n2nContext): DraftValueSelection {
				
		$tableName = $fetchDraftStmtBuilder->getTableName();
		$joinTableName = DraftMetaInfo::buildTableName($this->getEiEngine()->getEiThing(), EiFieldPath::from($this));
		
		$targetDraftDefinition = $this->getEiFieldRelation()->getTargetEiMask()->getEiEngine()->getDraftDefinition();
		$targetFetchDraftStmtBuilder = $targetDraftDefinition->createFetchDraftStmtBuilder($dm, $n2nContext, 't');
		$targetStmtBuilder = $targetFetchDraftStmtBuilder->getSelectStatementBuilder();
		
		$phName = $targetFetchDraftStmtBuilder->createPlaceholderName();
		$targetStmtBuilder->addJoin(JoinType::INNER, new QueryTable($joinTableName), 't')
				->match(new QueryColumn(DraftMetaInfo::COLUMN_ID, 't'), '=', 
						new QueryColumn(self::JT_TARGET_DRAFT_ID_COLUMN, 'jt'));
				
		$targetStmtBuilder->getWhereComparator()->match(new QueryColumn(self::JT_DRAFT_ID_COLUMN, 'jt'), 
				'=', new QueryPlaceMarker($phName));
		$targetDraftFetcher = $dm->createDraftFetcher($targetFetchDraftStmtBuilder, 
				$this->getEiFieldRelation()->getTargetEiSpec(), $targetDraftDefinition);
		
		return new EmbeddedToManyDraftValueSelection($fetchDraftStmtBuilder, $targetDraftFetcher, $phName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($targetDraft, $oldTargetDraft,
			PersistDraftStmtBuilder $persistDraftStmtBuilder, PersistDraftAction $persistDraftAction) {
		throw new NotYetImplementedException();
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
	 */
	public function supplyRemoveDraftStmtBuilder($targetDraft, $oldTargetDraft, RemoveDraftAction $removeDraftAction) {
		throw new NotYetImplementedException();
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
