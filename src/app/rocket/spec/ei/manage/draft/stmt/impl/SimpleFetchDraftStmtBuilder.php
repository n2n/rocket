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
namespace rocket\spec\ei\manage\draft\stmt\impl;

use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\Pdo;
use rocket\spec\ei\EiFieldPath;
use n2n\persistence\meta\data\QueryColumn;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\DraftMetaInfo;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\draft\stmt\DraftValuesResult;
use n2n\persistence\orm\property\BasicEntityProperty;
use n2n\persistence\PdoStatement;
use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\meta\data\SelectStatementBuilder;
use rocket\spec\ei\manage\draft\Draft;

class SimpleFetchDraftStmtBuilder implements FetchDraftStmtBuilder {
	const DRAF_COLUMN_PREFIX = 'd';

	private $pdo;
	private $idEntityProperty;
	private $selectBuilder;
	private $tableName;
	private $tableAlias;
	private $aliasBuilder;
	
	private $idAlias;
	private $entityObjIdAlias;
	private $lastModAlias;
	private $flagAlias;	
	private $userIdAlias;
	
	private $boundIdRawValue;
	private $boundEntityObjIdRawValue;
	private $boundLastModRawValue;
	private $boundFlagRawValue;
	private $boundUserIdRawValue;
	private $draftValueSelections = array();

	public function __construct(Pdo $pdo, string $tableName, BasicEntityProperty $idEntityProperty, string $tableAlias = null) {
		$this->pdo = $pdo;
		$this->idEntityProperty = $idEntityProperty;
		$this->selectBuilder = $pdo->getMetaData()->createSelectStatementBuilder();
		$this->selectBuilder->addFrom(new QueryTable($tableName), $tableAlias);
		$this->tableName = $tableName;
		$this->tableAlias = $tableAlias;
		$this->aliasBuilder = new AliasBuilder();

		$this->idAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_ID);
		$this->entityObjIdAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_ENTIY_OBJ_ID);
		$this->flagAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_FLAG);
		$this->userIdAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_USER_ID);
		$this->lastModAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_LAST_MOD);

		$this->selectBuilder->addSelectColumn($this->getDraftIdQueryItem(), $this->idAlias);
		$this->selectBuilder->addSelectColumn($this->getEntityObjIdQueryItem(), $this->entityObjIdAlias);
		$this->selectBuilder->addSelectColumn($this->getFlagQueryItem(), $this->flagAlias);
		$this->selectBuilder->addSelectColumn($this->getUserIdQueryItem(), $this->userIdAlias);
		$this->selectBuilder->addSelectColumn($this->getLastModQueryItem(), $this->lastModAlias);
	}

	/**
	 * @return Pdo
	 */
	public function getPdo(): Pdo {
		return $this->pdo;
	}

	/**
	 * @return \n2n\persistence\meta\data\SelectStatementBuilder
	 */
	public function getSelectStatementBuilder(): SelectStatementBuilder {
		return $this->selectBuilder;
	}
	
	public function createPlaceholderName(): string {
		return $this->aliasBuilder->createPlaceholderName();
	}
	
	public function getTableName(): string {
		return $this->tableName;
	}
	
	public function getTableAlias() {
		return $this->tableAlias;
	}

	/**
	 * @param EiFieldPath $eiFieldPath
	 * @return string column alias
	 */
	public function requestColumn(EiFieldPath $eiFieldPath): string {
		$columnName = DraftMetaInfo::buildDraftColumnName($eiFieldPath);
		$columnAlias = $this->aliasBuilder->createColumnAlias($columnName);
		$this->selectBuilder->addSelectColumn(new QueryColumn($columnName, $this->tableAlias), $columnAlias);
		return $columnAlias;
	}

	public function putDraftValueSelection(EiFieldPath $eiFieldPath, DraftValueSelection $draftValueSelection) {
		$this->draftValueSelections[(string) $eiFieldPath] = $draftValueSelection;
	}
	
	public function buildPdoStatement(): PdoStatement {
		$stmt = $this->pdo->prepare($this->selectBuilder->toSqlString());
		
		$stmt->bindColumn($this->idAlias, $this->boundIdRawValue);
		$stmt->bindColumn($this->entityObjIdAlias, $this->boundEntityObjIdRawValue);
		$stmt->bindColumn($this->lastModAlias, $this->boundLastModRawValue);
		$stmt->bindColumn($this->flagAlias, $this->boundFlagRawValue);
		$stmt->bindColumn($this->userIdAlias, $this->boundUserIdRawValue);
		
		foreach ($this->draftValueSelections as $draftValueSelection) {
			$draftValueSelection->bind($stmt);	
		}
		
		return $stmt;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder::getDraftIdQueryItem()
	 */
	public function getDraftIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_ID, $this->tableAlias);
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder::getLastModQueryItem()
	 */
	public function getLastModQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_LAST_MOD, $this->tableAlias);
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder::getEntityObjIdQueryItem()
	 */
	public function getEntityObjIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_ENTIY_OBJ_ID, $this->tableAlias);
	}
	
	public function getFlagQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_FLAG, $this->tableAlias);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder::getUserIdQueryItem()
	*/
	public function getUserIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_USER_ID, $this->tableAlias);
	}
	
	public function getIdAlias(): string {
		return $this->idAlias;
	}
	
	public function getEntityObjIdAlias(): string {
		return $this->entityObjIdAlias;
	}
	
	public function getFlagAlias(): string {
		return $this->flagAlias;
	}
	
	public function getLastModAlias(): string {
		return $this->lastModAlias;
	}
	
	public function getUserIdAlias(): string {
		return $this->userIdAlias;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder::buildResult()
	 */
	public function buildResult(): DraftValuesResult {
		$flag = null;
		if (in_array((string) $this->boundFlagRawValue, Draft::getFlags(), true)) {
			$flag = (string) $this->boundFlagRawValue;
		}
		
		$values = array();
		foreach ($this->draftValueSelections as $eiFieldPathStr => $draftValueSelection) {
			$values[$eiFieldPathStr] = $draftValueSelection->buildDraftValue();
		}
		
		return new DraftValuesResult($this->boundIdRawValue, 
				$this->idEntityProperty->parseValue($this->boundEntityObjIdRawValue, $this->pdo), 
				$this->pdo->getMetaData()->getDialect()->getOrmDialectConfig()
						->parseDateTime($this->boundLastModRawValue), 
				$flag, $this->boundUserIdRawValue, $values);
	}

}
