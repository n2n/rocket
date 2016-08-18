<?php
namespace rocket\script\entity\adaptive;

use n2n\persistence\orm\criteria\querypoint\MetaGenerator;
use n2n\persistence\orm\EntityModel;

class PrefixMetaGenerator implements MetaGenerator {
	private $tablePrefix;
	private $columnPrefix;

	public function __construct($tablePrefix, $columnPrefix) {
		$this->tablePrefix = $tablePrefix;
		$this->columnPrefix = $columnPrefix;
	}

	public function generateTableName(EntityModel $entityModel) {
		return $this->tablePrefix . $entityModel->getTableName();
	}

	public function generateColumnName(EntityModel $entityModel, $columnName) {
		return $this->columnPrefix . $columnName;
	}
}