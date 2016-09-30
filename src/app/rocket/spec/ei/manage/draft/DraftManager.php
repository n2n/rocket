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

use rocket\spec\config\SpecManager;
use n2n\persistence\orm\EntityManager;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\draft\DraftFetcher;
use rocket\spec\ei\manage\draft\DraftDefinition;
use rocket\spec\ei\manage\draft\DraftingContext;
use n2n\persistence\orm\ClosurePdoListener;
use n2n\persistence\TransactionEvent;
use n2n\persistence\orm\TransactionRequiredException;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\Pdo;
use rocket\spec\ei\EiSpec;

class DraftManager {
	private $specManager;
	private $em;
	private $n2nContext;
	private $draftingContext;
	private $draftActionQueue;
	private $pdoListener;

	public function __construct(SpecManager $specManager, EntityManager $em, N2nContext $n2nContext) {
		$this->specManager = $specManager;
		$this->em = $em;
		$this->n2nContext = $n2nContext;
		$this->draftingContext = new DraftingContext();
		$this->draftActionQueue = new DraftActionQueue($this->draftingContext, $em, $n2nContext);
		
		$that = $this;
		$em->getPdo()->registerListener($this->pdoListener = new ClosurePdoListener(function (TransactionEvent $e) 
				use ($that, $em) {
			
			// @todo maybe create TransactionalManager interface and combine mit lazyentitymanager
			$transaction = $e->getTransaction();
			$type = $e->getType();
				
			if ($type == TransactionEvent::TYPE_ON_COMMIT && $that->isOpen()
					&& ($transaction === null || !$transaction->isReadOnly())) {
				$that->flush();
			}
				
			if ($em->getScope() == EntityManager::SCOPE_TRANSACTION
					&& ($type == TransactionEvent::TYPE_ON_COMMIT || $type == TransactionEvent::TYPE_ON_ROLL_BACK)) {
				$that->close();
			}
		}));
	}
	
	public function getEntityManager(): EntityManager {
		return $this->em;
	}
	
	private function getDraftDefinitionByEiSpec(EiSpec $eiSpec) {
		return $eiSpec->getEiMaskCollection()->getOrCreateDefault()->getEiEngine()->getDraftDefinition();
	}
	
	private function getDraftDefinitionByEntityObj($entityObj) {
		$entityModel = $this->em->getEntityModelManager()->getEntityModelByEntityObj($entityObj);
		return $this->specManager->getEiSpecByClass($entityModel->getClass())->getEiMaskCollection()
				->getOrCreateDefault()->getDraftDefinition();		
	}
	
	public function find(\ReflectionClass $class, $draftId, DraftDefinition $draftDefinition = null) {
		$this->ensureDraftManagerOpen();
		
		$eiSpec = $this->specManager->getEiSpecByClass($class);
		if ($draftDefinition === null) {
			$draftDefinition = $this->getDraftDefinitionByEiSpec($eiSpec);
		}
		
		$stmtBuilder = $draftDefinition->createFetchDraftStmtBuilder($this, $this->n2nContext);
		$restrictedStmtBuilder = new RestrictedSelectDraftStmtBuilder($stmtBuilder);
		$restrictedStmtBuilder->restrictToDraftId($draftId);
		
		$draftFetcher = new DraftFetcher($stmtBuilder, $eiSpec, $draftDefinition, $this->draftingContext, $this->em);
		return $draftFetcher->fetchSingle();
	}
	
	public function findByEntityObjId(\ReflectionClass $class, $entityObjId, int $limit = null, int $num = null, 
			DraftDefinition $draftDefinition = null) {
		$this->ensureDraftManagerOpen();
		
		$eiSpec = $this->specManager->getEiSpecByClass($class);
		if ($draftDefinition === null) {
			$draftDefinition = $this->getDraftDefinitionByEiSpec($eiSpec);
		}
		
		$stmtBuilder = $draftDefinition->createFetchDraftStmtBuilder($this, $this->n2nContext);
		$restrictedStmtBuilder = new RestrictedSelectDraftStmtBuilder($stmtBuilder);
		$restrictedStmtBuilder->restrictToEntityObjId($entityObjId);
		$restrictedStmtBuilder->limit($limit, $num);
		$restrictedStmtBuilder->order();
	
		$draftFetcher = new DraftFetcher($stmtBuilder, $eiSpec, $draftDefinition, $this->draftingContext, $this->em);
		$draftFetcher->setStmt($restrictedStmtBuilder->buildPdoStatement());
		return $draftFetcher->fetch();
	}
	
	public function findByFilter(\ReflectionClass $class, $entityObjId = null, string $flag = null, bool $listed = null, 
			int $userId = null, int $limit = null, int $num = null, DraftDefinition $draftDefinition = null) {
		$this->ensureDraftManagerOpen();

		$eiSpec = $this->specManager->getEiSpecByClass($class);
		if ($draftDefinition === null) {
			$draftDefinition = $this->getDraftDefinitionByEiSpec($eiSpec);
		}

		$stmtBuilder = $draftDefinition->createFetchDraftStmtBuilder($this, $this->n2nContext);
		$restrictedStmtBuilder = new RestrictedSelectDraftStmtBuilder($stmtBuilder);
		if ($entityObjId !== null) {
			$restrictedStmtBuilder->restrictToEntityObjId($entityObjId);
		}
		
		if ($flag !== null) {
			$restrictedStmtBuilder->restrictToFlag($limit, $num);
		}
		
		if ($listed !== null) {
			$restrictedStmtBuilder->restrictToListed($listed);
		}
		
		if ($userId !== null) {
			$restrictedStmtBuilder->restrictToUserId($userId);
		}
		
		$restrictedStmtBuilder->limit($limit, $num);
		$restrictedStmtBuilder->order();

		$draftFetcher = new DraftFetcher($stmtBuilder, $eiSpec, $draftDefinition, $this->draftingContext, $this->em);
		$draftFetcher->setStmt($restrictedStmtBuilder->buildPdoStatement());
		return $draftFetcher->fetch();
	}

	public function countUnbounds(\ReflectionClass $class, DraftDefinition $draftDefinition = null) {
		$this->ensureDraftManagerOpen();
		
		if ($draftDefinition === null) {
			$draftDefinition = $this->getDraftDefinitionByEiSpec(
					$this->specManager->getEiSpecByClass($class));
		}
		
		$stmtBuilder = $draftDefinition->createCountDraftStmtBuilder($this, $this->n2nContext);
		$restrictedStmtBuilder = new RestrictedSelectDraftStmtBuilder($stmtBuilder);
		$restrictedStmtBuilder->restrictToUnbound(true);

		$stmt = $stmtBuilder->buildPdoStatement();
		$stmt->execute();
		$stmt->fetch(Pdo::FETCH_BOUND);
		
		return $stmtBuilder->buildResult();
	}
	
	public function findUnbounds(\ReflectionClass $class, int $limit, int $num = null, DraftDefinition $draftDefinition = null) {
		$this->ensureDraftManagerOpen();
		
		$eiSpec = $this->specManager->getEiSpecByClass($class);
		if ($draftDefinition === null) {
			$draftDefinition = $this->getDraftDefinitionByEiSpec($eiSpec);
		}
		
		$stmtBuilder = $draftDefinition->createFetchDraftStmtBuilder($this, $this->n2nContext);
		$restrictedStmtBuilder = new RestrictedSelectDraftStmtBuilder($stmtBuilder);
		$restrictedStmtBuilder->restrictToUnbound(true);
		$restrictedStmtBuilder->limit($limit, $num);
		$restrictedStmtBuilder->order();
		
		$draftFetcher = new DraftFetcher($stmtBuilder, $eiSpec, $draftDefinition, $this->draftingContext, $this->em);
		return $draftFetcher->fetch();
	}
	
	public function persist(Draft $draft, DraftDefinition $draftDefinition = null) {
		$this->ensureTransactionOpen('Perist');
		
		if ($draftDefinition === null && $draft->isNew()) {
			$draftDefinition = $this->getDraftDefinitionByEntityObj($draft->getDraftedEntity());
		}
		
		$this->draftActionQueue->persist($draft, $draftDefinition);
	}

	public function remove(Draft $draft) {
		$this->ensureTransactionOpen('Remove');
		
		$this->draftActionQueue->remove($draft);
	}
	
	public function flush() {
		$this->ensureTransactionOpen('Flush');
		
		$this->draftingContext->persistContext($this->draftActionQueue);
		
		$this->draftActionQueue->execute();
	}
	

	private function ensureDraftManagerOpen() {
		if ($this->draftingContext !== null) return;
	
		throw new IllegalStateException('DraftManager closed');
	}
	
	private function ensureTransactionOpen($operationName) {
		$this->ensureDraftManagerOpen();
		
		$pdo = $this->em->getPdo();
	
		if (!$pdo->inTransaction()) {
			throw new TransactionRequiredException($operationName
					. ' operation requires transaction.');
		}
	
		$transactionManager = $pdo->getTransactionManager();
		if ($transactionManager === null) return;
	
		if ($transactionManager->isReadyOnly()) {
			throw new IllegalStateException($operationName
					. ' operation disallowed in ready only transaction.');
		}
	}
	
	public function isOpen() {
		return $this->specManager !== null;
	}
	
	public function close() {
		if ($this->specManager === null) return;
	
		$this->em->getPdo()->unregisterListener($this->pdoListener);
		
		$this->specManager = null;
		$this->em = null;
		$this->draftingContext = null;
		$this->draftingContext = null;
		$this->draftActionQueue = null;
	}
}
