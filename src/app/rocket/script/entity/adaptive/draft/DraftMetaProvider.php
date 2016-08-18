<?php
namespace rocket\script\entity\adaptive\draft;

use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\meta\structure\Size;
use n2n\persistence\Pdo;
use n2n\persistence\meta\Database;
use n2n\persistence\orm\store\ActionJobMetaItem;
use rocket\script\entity\EntityScript;
use n2n\persistence\orm\store\ActionJobMeta;
use rocket\script\entity\field\DraftableScriptField;
use rocket\script\entity\adaptive\AdaptiveActionJobMeta;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\orm\EntityModel;

class DraftMetaProvider {
	const ID_COLUMN = 'id';
	const PRI_INDEX_NAME = 'id';
	const LAST_MOD_COLUMN = 'last_mod';
	const PUBLISHED_FLAG_COLUMN = 'published';
	const LAST_MOD_BY_COLUMN = 'last_mod_by';
	const NAVIGATABLE_COLUMN = 'navigatable';
	const ENTITY_ID_COLUMN = 'entity_id';
	const COLUMN_PREFIX = 'h_';
	const TABLE_PREFIX = 'rocket_draft_';
	const SEQUENCE_SUFFIX = '_sequence';
	
	private $dbh;
	private $entityScript;
	private $usedScriptFields = array();
	private $idColumn;
	
	public function __construct(Pdo $dbh, EntityScript $entityScript) {
		$this->dbh = $dbh;
		$this->entityScript = $entityScript;
	}
	
	public function check() {
		$this->usedScriptFields = array();
		
		$metaData = $this->dbh->getMetaData();
		$database = $metaData->getDatabase();
		
		$entityScript = $this->entityScript->getTopEntityScript();
		$entityModel = $entityScript->getEntityModel();
		$actionJobMeta = self::createAdaptiveActionJobMeta($entityModel->createActionJobMeta(), true);
		$actionJobMeta->setMetaRawValue(self::ID_COLUMN, null);
		$actionJobMeta->setMetaRawValue(self::ENTITY_ID_COLUMN, $entityModel->getIdProperty()->getColumnName());
		$actionJobMeta->setMetaRawValue(self::LAST_MOD_COLUMN, null);
		$actionJobMeta->setMetaRawValue(self::LAST_MOD_BY_COLUMN, null);
		$actionJobMeta->setMetaRawValue(self::PUBLISHED_FLAG_COLUMN, null);
		$actionJobMeta->setMetaRawValue(self::NAVIGATABLE_COLUMN, null);
	
		$items = new \ArrayObject();
		if (!$this->checkMetaF($entityScript, $items, $actionJobMeta)) {
			foreach ($actionJobMeta->getItems() as $item) {
				$draftTableName = $item->getTableName();
					
				if ($database->containsMetaEntityName($draftTableName)) {
					$database->removeMetaEntityByName($draftTableName);
				}
			}
				
			$database->flush();
			return false;
		}	;
		
		foreach ($items as $tableName => $item) {
			$this->checkTable($database, $tableName, $item);
		}	
				
		return true;
	}
	
	public function getIdColumn() {
		return $this->idColumn;
	}
	
	public function getUsedScriptFields() {
		return $this->usedScriptFields;
	}
	
	private function checkMetaF(EntityScript $entityScript, \ArrayObject $items, ActionJobMeta $meta) {
		$entityModel = $entityScript->getEntityModel();
		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
			if (!($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled())) {
				continue;
			}
				
			$columnName = $scriptField->getDraftColumnName();
			if (isset($columnName)) {
				$meta->setRawValue($entityModel, $columnName, $columnName);
			}
				
			$scriptField->checkDraftMeta($this->dbh);
			$this->usedScriptFields[] = $scriptField;
		}
		
		$this->mergeItems($items, $meta->getItems());
		
		
		$check = $entityScript->isDraftEnabled(); 
		foreach ($entityScript->getSubEntityScripts() as $subEntityScript) {
			$actionJobMeta = self::createAdaptiveActionJobMeta(
					$subEntityScript->getEntityModel()->createActionJobMeta(), true);
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
	
	private function checkTable(Database $database, $tableName, ActionJobMetaItem $metaItem) {
		$table = $database->getMetaEntityByName($tableName);
		self::ensureMetaEntityIsTable($table);
		$draftTableName = $metaItem->getTableName();

		$draftTable = null;
		if ($database->containsMetaEntityName($draftTableName)) {
			$draftTable = $database->getMetaEntityByName($draftTableName);
			self::ensureMetaEntityIsTable($draftTable);
		} else {
			$draftTable = $database->createMetaEntityFactory()->createTable($draftTableName);
		}
		
		$columnFactory = $draftTable->createColumnFactory();
		
		foreach ($metaItem->getRawValues() as $draftColumnName => $orignalColumnName) {
			switch ($draftColumnName) {
				case self::ID_COLUMN:
					if (!$draftTable->containsColumnName(self::ID_COLUMN)) {
						$idColumn = $columnFactory->createIntegerColumn(self::ID_COLUMN, Size::INTEGER, true);
						$this->dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($this->dbh, $idColumn,
								self::createSequenceName($metaItem->getEntityModel()));
						$draftTable->createIndex(IndexType::PRIMARY, array(self::ID_COLUMN));
					}
					
					$this->idColumn = $draftTable->getColumnByName(self::ID_COLUMN);
					break;
				case self::LAST_MOD_COLUMN:
					if (!$draftTable->containsColumnName(self::LAST_MOD_COLUMN)) {
						$columnFactory->createDateTimeColumn(self::LAST_MOD_COLUMN);
					}
					break;
				case self::LAST_MOD_BY_COLUMN:
					if (!$draftTable->containsColumnName(self::LAST_MOD_BY_COLUMN)) {
						$columnFactory->createIntegerColumn(self::LAST_MOD_BY_COLUMN, Size::INTEGER, false);
					}
					break;
				case self::PUBLISHED_FLAG_COLUMN:
					if (!$draftTable->containsColumnName(self::PUBLISHED_FLAG_COLUMN)) {
						$columnFactory->createIntegerColumn(self::PUBLISHED_FLAG_COLUMN, 1, true);
					}
					break;
				case self::NAVIGATABLE_COLUMN:
					if (!$draftTable->containsColumnName(self::NAVIGATABLE_COLUMN)) {
						$columnFactory->createIntegerColumn(self::NAVIGATABLE_COLUMN, 1, true);
					}
					break;
				default:
					$column = $table->getColumnByName($orignalColumnName);
					if ($draftTable->containsColumnName($draftColumnName)) {
						$draftColumn = $draftTable->getColumnByName($draftColumnName);
						if ($draftColumn->equalsType($column, true)) {
							break;
						}
					
						$draftTable->removeColumnByName($draftColumnName);
					}
					
					$draftColumn = $column->copy($draftColumnName);
					$draftColumn->setNullAllowed(true);
					$draftColumn->setValueGenerated(false);
					$draftTable->addColumn($draftColumn);
					
					if ($draftColumnName == self::ENTITY_ID_COLUMN) {
						$draftTable->createIndex(IndexType::INDEX, array(self::ENTITY_ID_COLUMN));
					}
					
					break;
			}	
		}
	}
	
	public static function createAdaptiveActionJobMeta(ActionJobMeta $decoratedMeta, $forMeta = false) {
		return new AdaptiveActionJobMeta($decoratedMeta, self::TABLE_PREFIX, self::COLUMN_PREFIX, true, 
				self::ID_COLUMN, self::createSequenceName($decoratedMeta->getEntityModel()), 
				self::ENTITY_ID_COLUMN, $forMeta);
	}

	public static function createDraftTableName($tableName) {
		return self::TABLE_PREFIX . $tableName;
	}

	public static function createSequenceName(EntityModel $entityModel) {
		return self::TABLE_PREFIX . $entityModel->getTopEntityModel()->getTableName() . self::SEQUENCE_SUFFIX;
	}
	
// 	public static function createHistoryColumnName($columnName) {
// 		return self::COLUMN_PREFIX . $columnName;
// 	}
	
	private static function ensureMetaEntityIsTable(MetaEntity $metaEntity) {
		if (!($metaEntity instanceof Table)) {
			throw new DraftMetaException('\'' . $metaEntity->getDatabase()->getName() . '\'.\'' . $metaEntity->getName()
					. '\' is no table (but propably a view)');
		}
	}
}