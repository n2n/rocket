<?php

namespace rocket\script\entity\adaptive\translation;

use rocket\script\entity\EntityScript;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\orm\store\ActionJobMeta;
use rocket\script\entity\adaptive\AdaptiveActionJobMeta;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\Pdo;
use rocket\script\entity\field\ScriptField;
use rocket\script\entity\field\TranslatableScriptField;
use n2n\persistence\meta\Database;
use n2n\persistence\orm\store\ActionJobMetaItem;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\meta\structure\Size;
use n2n\persistence\meta\structure\Index;

class TranslationMetaProvider {
	const TABLE_PREFIX = 'rocket_translation_';
	const SEQUENCE_SUFFIX = '_sequence';
	const ID_COLUMN = 'id';
	const LOCALE_COLUMN = 'locale';
	const LOCALE_COLUMN_LENGTH = 5;
	const ELEMENT_ID_COLUMN = 'element_id';
	const COLUMN_PREFIX = 't_';
	
	private $dbh;
	private $entityScript;
	private $referencedElementIdColumn;
	private $includedScriptFields;
	private $additionalTablePrefix;
	
	public function __construct(Pdo $dbh, EntityScript $entityScript, Column $referencedElementIdColumn = null, 
			array $includedScriptFields = null, $additionalTablePrefix = null) {
		$this->dbh = $dbh;
		$this->entityScript = $entityScript->getTopEntityScript();
		$this->referencedElementIdColumn = $referencedElementIdColumn;
		$this->includedScriptFields = $includedScriptFields;
		$this->additionalTablePrefix = $additionalTablePrefix;
	}
	
	public function check() {
		$metaData = $this->dbh->getMetaData();
		$database = $metaData->getDatabase();
		
		$entityModel = $this->entityScript->getEntityModel();
		$actionJobMeta = self::createAdaptiveActionJobMeta($entityModel->createActionJobMeta(), $this->additionalTablePrefix, true);
		$actionJobMeta->setMetaRawValue(self::ID_COLUMN, null);
		$actionJobMeta->setMetaRawValue(self::ELEMENT_ID_COLUMN, $entityModel->getIdProperty()->getColumnName());
		$actionJobMeta->setMetaRawValue(self::LOCALE_COLUMN, null);
		
		$items = new \ArrayObject();
		if (!$this->checkMetaF($this->entityScript, $items, $actionJobMeta)) {
			foreach ($items as $item) {
				$translationTableName = $this->createTranslationTableName($item->getTableName());
			
				if ($database->containsMetaEntityName($translationTableName)) {
					$database->removeMetaEntityByName($translationTableName);
				}
			}
			
			$database->flush();
			return;
		}
				
		foreach ($items as $tableName => $item) {
			$this->checkTable($database, $tableName, $item, $this->referencedElementIdColumn);
		}
		
		$database->flush();
	}
	
	private function checkMetaF(EntityScript $entityScript, \ArrayObject $items, ActionJobMeta $meta) {
		$entityModel = $entityScript->getEntityModel();
		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
			if (!$this->inScriptFieldIncluded($scriptField)) {
				continue;
			}
			
			$columnName = $scriptField->getTranslationColumnName();
			if (isset($columnName)) {
				$meta->setRawValue($entityModel, $columnName, $columnName);
			}
			
			$scriptField->checkTranslationMeta($this->dbh);
		}
		
		$this->mergeItems($items, $meta->getItems());
		
		$check = $entityScript->isTranslationEnabled(); 
		foreach ($entityScript->getSubEntityScripts() as $subEntityScript) {
			$actionJobMeta = self::createAdaptiveActionJobMeta(
					$subEntityScript->getEntityModel()->createActionJobMeta(),
					$this->additionalTablePrefix, true);
			$actionJobMeta->setMetaRawValue(self::ID_COLUMN, null);
			if (self::checkMetaF($subEntityScript, $items, $actionJobMeta)) {
				$check = true;
			}
		}
				
		return $check;
	}
	
	private function mergeItems(\ArrayObject $items, array $newItems) {
		foreach ($newItems as $tableName => $newItem) {
			if (isset($items[$tableName])) {
				$items[$tableName]->setRawValues(array_merge($items[$tableName]->getRawValues(), 
						$newItem->getRawValues()));
				continue;
			}
			
			$items[$tableName] = $newItem;
		}
	}
	
	private function inScriptFieldIncluded(ScriptField $scriptField) {
		if (!($scriptField instanceof TranslatableScriptField) || !$scriptField->isTranslationEnabled()) {
			return false;
		}
		
		if ($this->includedScriptFields === null) return true;
		
		foreach ($this->includedScriptFields as $includedScriptField) {
			if ($includedScriptField->equals($scriptField)) {
				return true;
			}
		}
		
		return false;
	}
	
	private function checkTable(Database $database, $tableName, ActionJobMetaItem $metaItem, Column $elementIdColumn = null) {
		$table = $database->getMetaEntityByName($tableName);
		self::ensureMetaEntityIsTable($table);
		$translationTableName = $metaItem->getTableName();
		$translationTable = null;
		if ($database->containsMetaEntityName($translationTableName)) {
			$translationTable = $database->getMetaEntityByName($translationTableName);
			self::ensureMetaEntityIsTable($translationTable);
		} else {
			$translationTable = $database->createMetaEntityFactory()->createTable($translationTableName);
		}
		
		$columnFactory = $translationTable->createColumnFactory();
		
		foreach ($metaItem->getRawValues() as $translationColumnName => $orignalColumnName) {
			switch ($translationColumnName) {
				case self::ID_COLUMN:
					if (!$translationTable->containsColumnName(self::ID_COLUMN)) {
						$idColumn = $columnFactory->createIntegerColumn(self::ID_COLUMN, Size::INTEGER, true);
						$this->dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($this->dbh, $idColumn,
								self::createSequenceName($translationTableName));
						$translationTable->createIndex(IndexType::PRIMARY, array(self::ID_COLUMN));
					}
					break;
				case self::LOCALE_COLUMN:
					if (!$translationTable->containsColumnName(self::LOCALE_COLUMN)) {
						$columnFactory->createStringColumn(self::LOCALE_COLUMN, self::LOCALE_COLUMN_LENGTH, true);
						$translationTable->createIndex(IndexType::INDEX, array(self::LOCALE_COLUMN));
					}
					break;
				default:
					$column = null;
					if ($translationColumnName == self::ELEMENT_ID_COLUMN && isset($elementIdColumn)) {
						$column = $elementIdColumn;
					} else { 
						if (!$table->containsColumnName($orignalColumnName)) break;
						$column = $table->getColumnByName($orignalColumnName);
					}
					
					if ($translationTable->containsColumnName($translationColumnName)) {
						$translationColumn = $translationTable->getColumnByName($translationColumnName);
						if ($translationColumn->equalsType($column, true)) {
							break;
						}
					
						$translationTable->removeColumnByName($translationColumnName);
					}
					
					$translationColumn = $column->copy($translationColumnName);
					$translationColumn->setNullAllowed(true);
					$translationColumn->setValueGenerated(false);
					$translationTable->addColumn($translationColumn);
					
					if ($translationColumnName == self::ELEMENT_ID_COLUMN 
							&& !$this->containsIndexColumnName($translationTable, self::ELEMENT_ID_COLUMN)) {
						$translationTable->createIndex(IndexType::INDEX, array(self::ELEMENT_ID_COLUMN));
					}
					break;
			}
		}

		if ($translationTable->containsColumnName(self::ELEMENT_ID_COLUMN) 
				&& $translationTable->containsColumnName(self::LOCALE_COLUMN)
				&& !$this->containsUniqueIndexColumnNames($translationTable, 
						array(self::ELEMENT_ID_COLUMN, self::LOCALE_COLUMN))) {
			$translationTable->createIndex(IndexType::UNIQUE, 
					array(self::ELEMENT_ID_COLUMN, self::LOCALE_COLUMN));
		}
	}
	
	private function containsIndexColumnName(Table $table, $columnName) {
		foreach ($table->getIndexes() as $index) {
			if ($index->containsColumnName($columnName)
					&& $index->getType() == IndexType::INDEX
					&& count($index->getColumns()) == 1) return true;
		}
		return false;
	}
	
	private function containsUniqueIndexColumnNames(Table $table, array $columnNames) {
		foreach ($table->getIndexes() as $index) {
			if ($this->containsColumnNamesInIndex($index, $columnNames) 
					&& $index->getType() == IndexType::UNIQUE
					&& count($index->getColumns()) == count($columnNames)) {
				return true;
			}
		}
		
		return false;
	}
	
	private function containsColumnNamesInIndex(Index $index, array $columnNames) {
		foreach ($columnNames as $columnName) {
			if (!$index->containsColumnName($columnName)) return false;
		}
		
		return true;
	}

	private function createTranslationTableName($tableName) {
		return self::TABLE_PREFIX . $this->additionalTablePrefix . $tableName;
	}

	private static function createSequenceName($tableName) {
		return $tableName . self::SEQUENCE_SUFFIX;
	}

	private static function createTranslationColumName($columnName) {
		return self::COLUMN_PREFIX . $columnName;
	}
	
	public static function createAdaptiveActionJobMeta(ActionJobMeta $decoratedMeta, $additionalTablePrefix = null, $forMeta = false) {
		return new AdaptiveActionJobMeta($decoratedMeta, self::TABLE_PREFIX . $additionalTablePrefix, self::COLUMN_PREFIX, true,
				self::ID_COLUMN, self::createSequenceName(self::TABLE_PREFIX . $decoratedMeta->getEntityModel()->getTableName()),
				null, $forMeta);
	}
	
	private static function ensureMetaEntityIsTable(MetaEntity $metaEntity) {
		if (!($metaEntity instanceof Table)) {
			throw new TranslationMetaException('\'' . $metaEntity->getDatabase()->getName() 
					. '\'.\'' . $metaEntity->getName() . '\' is no table (but propably a view)');
		}
	}
}