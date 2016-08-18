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
namespace rocket\spec\ei\manage\draft;

use n2n\persistence\Pdo;
use rocket\spec\ei\EiFieldPath;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\draft\stmt\impl\SimplePersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\impl\SimpleRemoveDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\impl\SimpleFetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\DraftValuesResult;
use n2n\reflection\ArgUtils;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\draft\stmt\impl\SimpleCountDraftStmtBuilder;

class DraftDefinition {
	const ALIAS = 'd';
	
	private $tableName;
	private $entityModel;
	private $draftProperties = array();
	
	public function __construct(string $tableName, EntityModel $entityModel) {
		$this->tableName = $tableName;
		$this->entityModel = $entityModel;
	}
	
	public function getTableName(): string {
		return $this->tableName;
	}
	
	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}

	public function putDraftProperty($id, DraftProperty $draftProperty) {
		return $this->draftProperties[$id] = $draftProperty;
	}
	
	public function getDraftProperties() {
		return $this->draftProperties;
	}
	
	public function isEmpty() {
		return empty($this->draftProperties);
	}
	
	public function createCountDraftStmtBuilder(DraftManager $dm, N2nContext $n2nContext, $tableAlias = null) {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		return new SimpleCountDraftStmtBuilder($dm->getEntityManager()->getPdo(), $this->tableName,
				$idEntityProperty, $tableAlias);
	}
	
	public function createFetchDraftStmtBuilder(DraftManager $dm, N2nContext $n2nContext, $tableAlias = null) {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		$stmtBuilder = new SimpleFetchDraftStmtBuilder($dm->getEntityManager()->getPdo(), $this->tableName, 
				$idEntityProperty, $tableAlias);
		
		foreach ($this->draftProperties as $id => $draftProperty) {
			$stmtBuilder->putDraftValueSelection(new EiFieldPath(array($id)), 
					$draftProperty->createDraftValueSelection($stmtBuilder, $dm, $n2nContext));
		}
		
		return $stmtBuilder;
	}
	
	public function createPersistDraftStmtBuilder(PersistDraftAction $persistDraftAction, 
			DraftActionQueue $draftActionQueue) {
		$draft = $persistDraftAction->getDraft();
		$pdo = $draftActionQueue->getEntityManager()->getPdo();
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		$stmtBuilder = new SimplePersistDraftStmtBuilder($pdo, $this->tableName, $idEntityProperty, $draft->getId());
		
		$draftingContext = $persistDraftAction->getQueue()->getDraftingContext();
		
		$empty = false;
		if (!$draft->isNew()) {
			$draftValuesResult = $draftingContext->getDraftValuesResultByDraft($draft);
			$empty = $draftValuesResult->getFlag() === $draft->getFlag() 
					&& $draftValuesResult->getLastMod() === $draft->getLastMod()
					&& $draftValuesResult->getUserId() === $draft->getUserId();
		}
		
		$stmtBuilder->setFlag($draft->getFlag());
		$stmtBuilder->setUserId($draft->getUserId());
		$stmtBuilder->setLastMod($draft->getLastMod());
		
		$liveEntry = $draft->getLiveEntry();
		if ($liveEntry->isPersistent()) { 
			$stmtBuilder->setDraftedEntityObjId($idEntityProperty->buildRaw($draft->getLiveEntry()->getId(), $pdo));
		}
		
		$draftValuesMap = $draft->getDraftValueMap();
		foreach ($this->draftProperties as $id => $draftProperty) {
			$eiFieldPath = new EiFieldPath(array($id));
			$draftProperty->supplyPersistDraftStmtBuilder($draftValuesMap->getValue($eiFieldPath), 
					$draftValuesResult->getValue($eiFieldPath), $stmtBuilder, $persistDraftAction);
		}
		
		if ($empty && $stmtBuilder->hasValues()) {
			$stmtBuilder->setNeedless(true);
			return $stmtBuilder;
		}
		
		$persistDraftAction->executeAtEnd(function () use ($draftingContext, $draft) {
			$newDraftValuesResult = new DraftValuesResult($draft->getId(), 
					($draft->getLiveEntry()->hasId() ? $draft->getLiveEntry()->getId() : null),
					$draft->getLastMod(), $draft->getFlag(), $draft->getUserId(), 
					$draft->getDraftValueMap()->getValues());
			$draftingContext->setDraftValuesResult($draft, $newDraftValuesResult);
		});
		
		return $stmtBuilder;
	}
	
	public function createRemoveDraftStmtBuilder(DraftAction $draftAction, DraftActionQueue $draftActionQueue) {
		$draft = $draftAction->getDraft();
		$statementBuilder = new SimpleRemoveDraftStmtBuilder($draftActionQueue->getEntityManager()->getPdo(), 
				$this->tableName, $draft->getId());
		
		$draftedEntityObj = $draft->getDraftedEntityObj();
		foreach ($this->draftProperties as $draftProperty) {
			$draftProperty->supplyRemoveDraftStmtBuilder($draftProperty->readDraftValue($draftedEntityObj),
					$statementBuilder, $draftAction, $draftProperty->readDraftValue($draftedEntityObj));
		}
		
		return $statementBuilder;
	}
	
	public function createDraftedEntityObj(DraftValuesResult $draftValuesResult, $baseEntityObj = null) {
		ArgUtils::valObject($baseEntityObj, true);
		
		$draftedEntityObj = ReflectionUtils::createObject($this->entityModel->getClass());
		if ($baseEntityObj !== null) {
			$this->entityModel->copy($baseEntityObj, $draftedEntityObj);
		}
		
		$values = $draftValuesResult->getValues();
		foreach ($this->draftProperties as $id => $draftProperty) {
			IllegalStateException::assertTrue(array_key_exists($id, $values)); 
			$draftProperty->writeDraftValue($draftedEntityObj, $values[$id]);
		}
		
		return $draftedEntityObj;
	}
}
