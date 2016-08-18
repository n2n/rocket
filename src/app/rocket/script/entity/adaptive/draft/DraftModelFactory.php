<?php
namespace rocket\script\entity\adaptive\draft;

use rocket\script\entity\adaptive\translation\TranslationModelFactory;
use rocket\script\entity\EntityScript;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\Pdo;

class DraftModelFactory {
	public static function createDraftModel(EntityManager $em, EntityScript $entityScript) {
		if (!$entityScript->isDraftEnabled()) {
			throw new DraftModelInitializionException('EntityScript \''
					. $entityScript->getId() . '\' is not draftable.');
		}
		
		$draftModel = new DraftModel($em, $entityScript->getTopEntityScript());
		
		if ($entityScript->isTranslationEnabled()) {
			$draftModel->setTranslationModel(TranslationModelFactory::createTranslationModel($em, $entityScript, true));	
		}
		
		return $draftModel;
	}
	
	public static function checkMeta(Pdo $dbh, EntityScript $entityScript) {
		$draftMetaChecker = new DraftMetaProvider($dbh, $entityScript);
		if ($draftMetaChecker->check()) {
			TranslationModelFactory::checkMeta($dbh, $entityScript, $draftMetaChecker->getIdColumn(),
					$draftMetaChecker->getUsedScriptFields(), DraftMetaProvider::TABLE_PREFIX);
		}
	}
	
}



// public static function createDraftModel(EntityManager $em, EntityScript $entityScript) {
// 	if (!$entityScript->isDraftEnabled()) {
// 		throw new DraftModelInitializionException('No history for EntityScript \''
// 				. $entityScript->getId() . '\' available.');
// 	}

// 	$em = $entityScript->lookupEntityManager(N2N::getDbhPool());
// 	$database = $em->getDbh()->getMetaData()->getDatabase();
// 	$entityModel = $entityScript->getEntityModel();
// 	$tableName = $entityModel->getTableName();
// 	$draftTableName = self::createDraftTableName($tableName, $entityScript);
// 	$table = $database->getMetaEntityByName($tableName);
// 	// @todo check if $table is instance of Table
// 	if (!($table instanceof Table)) {
// 		throw new DraftModelInitializionException('\'' . $database->getName() . '\'.\'' . $tableName
// 				. '\' is no table (but propably a view)');
// 	}

// 	$draftModel = new DraftModel($em, $entityModel, $draftTableName, self::ID_COLUMN, self::NAME_COLUMN,
// 			self::LAST_MOD_COLUMN, self::PUBLISHED_FLAG_COLUMN, self::ENTITY_ID_COLUMN);

// 	$historyScriptFields = array();
// 	foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
// 		if (!($scriptField instanceof DraftableScriptField) || $scriptField->isReadOnly()
// 		|| !$scriptField->getEntityScript()->isDraftable()) {
// 			continue;
// 		}
			
// 		$historyScriptFields[] = $scriptField;
// 		$draftModel->putDraftableScriptField($scriptField);
// 		$columnName = $scriptField->getDraftableColumnName();
// 		$draftModel->registerColumnName($columnName, self::createHistoryColumnName($columnName));
// 	}

// 	try {
// 		$draftModel->setTranslationModel(TranslationModelFactory::createFromScriptFields($em, $historyScriptFields, $draftTableName));
// 	} catch (TranslationModelNotAvailable $e) { }

// 	return $draftModel;
// }