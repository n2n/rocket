<?php
namespace rocket\script\entity\adaptive\translation;

use rocket\script\entity\EntityScript;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\Pdo;
use n2n\persistence\orm\EntityManager;

class TranslationModelFactory {

	public static function createTranslationModel(EntityManager $em, EntityScript $entityScript, $draftableOnly = false) {
		return new TranslationModel($em, $entityScript, $draftableOnly);
	}
	
	public static function checkMeta(Pdo $dbh, EntityScript $entityScript, Column $referencedElementIdColumn = null, 
			array $includedScriptFields = null, $additionalTablePrefix = null) {
		$tmc = new TranslationMetaProvider($dbh, $entityScript, $referencedElementIdColumn, $includedScriptFields,
				$additionalTablePrefix);
		$tmc->check();
	}
}

// 	public static function createFromProperties(EntityManager $em, array $properties, $translationTableNameBase) {
// 		$tableName = self::createTranslationTableName($translationTableNameBase);
// 		$translationModel = new TranslationModel($em, $tableName,
// 				self::ID_COLUMN, self::LOCALE_COLUMN, self::ELEMENT_ID_COLUMN);
		
// 		$numProperties = 0;
// 		foreach ($properties as $property) {
// 			if (!($property instanceof ColumnProperty)) {
// 				continue;
// 			}
// 			$columnName = $property->getColumnName();
// 			$translationModel->registerColumnName($columnName, self::createTranslationColumName($columnName));
// 			$numProperties++;
// 		}
		
// // 		if (!$numProperties) {
// // 			throw new TranslationModelNotAvailable('No properties to translate');
// // 		}
		
// 		return $translationModel;
// 	}
	
// 	public static function createFromScriptFields(EntityManager $em, array $scriptFields, $translationTableNameBase) {
// 		$properties = array();
// 		foreach ($scriptFields as $scriptField) {
// 			if ($scriptField instanceof TranslatableScriptField && $scriptField->isTranslationEnabled()) {
// 				$properties[] = $scriptField->getEntityProperty();
// 			}
// 		}
	
// 		$translationModel = self::createFromProperties($em, $properties, $translationTableNameBase);
// 		$translationModel->setPersistingAllowed(true);
		
// 		return $translationModel;
// 	}
	
// 	public static function createFromEntityScript(EntityScript $entityScript) {
// 		$tableNameBase = $entityScript->getTopEntityScript()->getEntityModel()->getTableName();

// 		return self::createFromScriptFields($entityScript->lookupEntityManager(N2N::getDbhPool()), 
// 				$entityScript->getFieldCollection()->toArray(), $tableNameBase);
// 	}
	
// 	public static function createFromEntityModel(EntityManager $em, EntityModel $entityModel) {
// 		$entityModel = $entityModel->getTopEntityModel();
	
// 		$properties = array();
// 		foreach ($entityModel->getAllProperties() as $property) {
// 			$properties[] = $property;
// 		}
	
// 		return self::createFromProperties($em, $properties, $entityModel->getTableName());
// 	}
	
// 	public static function checkMetaForEntityScript(EntityScript $entityScript) {
// 		$entityScript = $entityScript->getTopEntityScript();
		
// 		$em = $entityScript->lookupEntityManager(N2N::getDbhPool());
// 		$dbh = $em->getDbh();
		
// 		$scriptFields = array();
// 		$dropAllowed = true;

// 		foreach ($entityScript->getFieldCollection()->combineAll() as $scriptField) {
// 			$scriptFields[$scriptField->getId()] = $scriptField;
// 			// @todo could cause problems when scriptfield translation was disabled and column still exist.
// // 			if ($scriptField->getEntityScript()->isSealed()) {
// // 				$dropAllowed = false;
// // 			}
// 		}
		
// 		$topEntityModel = $entityScript->getEntityModel();
// 		$table = $dbh->getMetaData()->getDatabase()->getMetaEntityByName($topEntityModel->getTableName());
// 		$entryIdColumn = $table->getColumnByName($topEntityModel->getIdProperty()->getColumnName());
// 		// @todo check if $table is instance of Table
// 		self::checkMetaForScriptFields($dbh, $entryIdColumn, $scriptFields, $dropAllowed, $table->getName());
// 	}

// 	public static function checkMetaForScriptFields(Pdo $dbh, Column $entryIdColumn, array $scriptFields, $dropAllowed, $translationTableNameBase) {
// 		$metaData = $dbh->getMetaData();
// 		$database = $metaData->getDatabase();
		
// 		$translationScriptFields = array();
// 		foreach ($scriptFields as $scriptField) {
// 			if (!($scriptField instanceof TranslatableScriptField) || !$scriptField->isTranslationEnabled()) {
// 				continue;
// 			}
			
// 			$translationScriptFields[] = $scriptField;
// 		}
		
// 		$translationTableName = self::createTranslationTableName($translationTableNameBase);
// 		$translationTable = null;
// 		if ($database->containsMetaEntityName($translationTableName)) {
// 			if (!sizeof($translationScriptFields)) {
// 				if ($dropAllowed) {
// 					$database->removeMetaEntityByName($translationTableName);
// 					$database->flush();
// 				}
// 				return;
// 			}
			
// 			$translationTable = $database->getMetaEntityByName($translationTableName);
// 		} else {
// 			if (!sizeof($translationScriptFields)) return;
			
// 			$translationTable = $database->createMetaEntityFactory()->createTable($translationTableName);
// 			$columnFactory = $translationTable->createColumnFactory();
// 			$idColumn = $columnFactory->createIntegerColumn(self::ID_COLUMN, Size::INTEGER, true);
// 			$dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($dbh, $idColumn,
// 					self::createSequenceName($translationTable->getName()));
			
// 			$columnFactory->createStringColumn(self::LOCALE_COLUMN, self::LOCALE_COLUMN_LENGTH);
			
// 			$elementIdColumn = $entryIdColumn->copy(self::ELEMENT_ID_COLUMN);
// 			$elementIdColumn->setValueGenerated(false);
// 			$translationTable->addColumn($elementIdColumn);
			
// 			$translationTable->createIndex(IndexType::PRIMARY, array(self::ID_COLUMN));
// 			$translationTable->createIndex(IndexType::INDEX, array(self::LOCALE_COLUMN));
// 			$translationTable->createIndex(IndexType::INDEX, array(self::ELEMENT_ID_COLUMN));
// 		}
		
// 		$usedTranslationColumnNames = array(self::ID_COLUMN, self::LOCALE_COLUMN, self::ELEMENT_ID_COLUMN);
// 		foreach ($translationScriptFields as $translationScriptField) {
// 			$entityProperty = $translationScriptField->getEntityProperty();
// 			if (!($entityProperty instanceof ColumnProperty)) {
// 				continue;
// 			}
			
// 			$columnName = $entityProperty->getColumnName();
// 			$translationColumnName = self::createTranslationColumName($columnName);
			
// 			if (in_array($translationColumnName, $usedTranslationColumnNames)) continue;
// 			$usedTranslationColumnNames[] = $translationColumnName;
			
// 			$table = $database->getMetaEntityByName($entityProperty->getEntityModel()->getTableName());
// 			// @todo check if $table instance of Table
// 			$column = $table->getColumnByName($columnName);
			
// 			$translationColumn = null;
// 			if ($translationTable->containsColumnName($translationColumnName)) {
// 				$translationColumn = $translationTable->getColumnByName($translationColumnName);
// 				if ($column->equalsType($translationColumn)) {
// 					continue;
// 				}
// 				$translationTable->removeColumnByName($translationColumn->getName());
// 			}
			
// 			$translationColumn = $column->copy($translationColumnName);
// 			$translationColumn->setNullAllowed(true);
// 			$translationColumn->setValueGenerated(false);
// 			$translationTable->addColumn($translationColumn);
// 		}

// 		if ($dropAllowed) {
// 			foreach ($translationTable->getColumns() as $column) {
// 				if (in_array($column->getName(), $usedTranslationColumnNames)) continue;
					
// 				$translationTable->removeColumnByName($column->getName());
// 			}
// 		}

// 		$database->flush();
// 	}