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
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\draft\DraftDefinition;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use n2n\persistence\PdoStatement;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\EiSpec;
use n2n\persistence\orm\EntityManager;
use rocket\spec\ei\manage\draft\stmt\DraftValuesResult;

class DraftFetcher {
	private $fetchDraftStmtBuilder;
	private $eiSpec;
	private $stmt;
	private $draftDefinition;
	private $draftingContext;
	private $em;
	private $bindValues = array();
	private $entityObj;
	
	public function __construct(FetchDraftStmtBuilder $selectDraftStmtBuilder, EiSpec $eiSpec, 
			DraftDefinition $draftDefinition, DraftingContext $draftingContext, EntityManager $em) {
		$this->fetchDraftStmtBuilder = $selectDraftStmtBuilder;
		$this->eiSpec = $eiSpec;
		$this->draftDefinition = $draftDefinition;
		$this->draftingContext = $draftingContext;
		$this->em = $em;
	}
	
	public function getFetchDraftStmtBuilder() {
		return $this->fetchDraftStmtBuilder;
	}

	public function setStmt(PdoStatement $stmt) {
		$this->stmt = $stmt;
	}
	
	/**
	 * @return object 
	 */
	public function getEntityObj() {
		return $this->entityObj;
	}
	
	/**
	 * @param object $baseEntityObj
	 */
	public function setEntityObj($entityObj) {
		$this->entityObj = $entityObj;
	}
	
	/**
	 * @throws DraftingException
	 * @return Draft
	 */
	public function fetchSingle() {
		$drafts = $this->fetch();
		$numDrafts = count($drafts);
		if ($numDrafts > 1) {
			throw new DraftingException('Multiple results.');
		}
		
		if ($numDrafts == 0) {
			return null;
		}
		
		return current($drafts);
	}
	
	/**
	 * @throws DraftingException
	 * @return Draft[]
	 */
	public function fetch() {
		if ($this->stmt === null) {
			$this->stmt = $this->fetchDraftStmtBuilder->buildPdoStatement();
		}
		
		$this->stmt->execute();
		
		$drafts = array();
		while ($this->stmt->fetch(Pdo::FETCH_BOUND)) {
			$draftValuesResult = $this->fetchDraftStmtBuilder->buildResult();
			if ($this->draftingContext->containsDraftId($this->draftDefinition, $draftValuesResult->getId())) {
				$drafts[] = $this->draftingContext->getDraftById($this->draftDefinition, $draftValuesResult->getId());
				continue;
			}
						
			$draft = $this->createDraft($draftValuesResult);
			
			$this->draftingContext->add($this->draftDefinition, $draft, $draftValuesResult);
			$drafts[] = $draft;
		}
		
		return $drafts;
	}
	
	private function createDraft(DraftValuesResult $draftValuesResult) {
		$entityObjId = $draftValuesResult->getEntityObjId();
		
		$entityObj = null;
		if (null !== $entityObjId) {
			$entityObj = $this->em->find($this->eiSpec->getEntityModel()->getClass(), $entityObjId);
		}
		
		$liveEntry = null;
		if ($entityObj !== null) {
			$liveEntry = LiveEntry::createFrom($this->eiSpec, $entityObj);
		} else {
			$liveEntry = LiveEntry::createNew($this->eiSpec);
			if ($entityObjId !== null) {
				$this->eiSpec->getEntityModel()->getIdDef()->getEntityProperty()
						->writeValue($liveEntry->getEntityObj(), $entityObjId);
			}
		}
		$draft = new Draft($draftValuesResult->getId(), $liveEntry, $draftValuesResult->getLastMod(),
				$draftValuesResult->getUserId(), new DraftValueMap($draftValuesResult->getValues()));
		$draft->setType($draftValuesResult->getType());
		
		return $draft;
	}
}
