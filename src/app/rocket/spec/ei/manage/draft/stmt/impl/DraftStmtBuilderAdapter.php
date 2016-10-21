<?php

namespace rocket\spec\ei\manage\draft\stmt\impl;

use rocket\spec\ei\manage\draft\stmt\DraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\impl\AliasBuilder;
use n2n\persistence\PdoStatement;
use n2n\persistence\Pdo;

abstract class DraftStmtBuilderAdapter implements DraftStmtBuilder {
	protected $pdo;
	protected $tableName;
	protected $boundValues = array();
	protected $aliasBuilder;

	public function __construct(Pdo $pdo, string $tableName) {
		$this->pdo = $pdo;
		$this->tableName = $tableName;
		$this->aliasBuilder = new AliasBuilder();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\stmt\DraftStmtBuilder::getPdo()
	 */
	public function getPdo(): Pdo {
		return $this->pdo;
	}

	public function getTableName(): string {
		return $this->tableName;
	}
	
	public function createPlaceholderName(): string {
		return $this->aliasBuilder->createPlaceholderName();
	}
	
	public function bindValue(string $placeholderName, $value) {
		$this->boundValues[$placeholderName] = $value;
	}
	
	protected function applyBoundValues(PdoStatement $stmt) {
		foreach ($this->boundValues as $placeholderName => $value) {
			$stmt->bindValue($placeholderName, $value);
		}
	}

}