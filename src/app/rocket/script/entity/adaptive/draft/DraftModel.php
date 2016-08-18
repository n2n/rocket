<?php
namespace rocket\script\entity\adaptive\draft;

use n2n\persistence\meta\data\SelectStatementBuilder;
use rocket\script\entity\adaptive\translation\TranslationModel;
use n2n\persistence\orm\EntityManager;
use rocket\script\entity\field\DraftableScriptField;
use n2n\persistence\orm\OrmUtils;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\criteria\CriteriaState;
use n2n\persistence\orm\store\PersistenceContextImpl;
use rocket\script\entity\adaptive\AdaptiveQueryPoint;
use n2n\persistence\orm\EntityModelManager;
use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\store\QueuePersistingJob;
use n2n\persistence\orm\store\PersistenceActionQueueImpl;
use n2n\persistence\orm\InheritanceTypeChanger;
use rocket\script\entity\field\TranslatableScriptField;
use n2n\persistence\orm\store\RemoveActionQueueImpl;
use n2n\persistence\orm\store\QueueRemovingJob;
use n2n\persistence\orm\EntityModel;

class DraftModel {
	private $draftModel;	
	private $entityModel;
	
	public function __construct(DraftManager $draftManager, EntityModel $entityModel) {
		$this->draftManager = $draftManager;
		$this->entityModel = $entityModel;
	}
	
	private function createCriteriaState(Entity $baseEntity) {
		$criteriaState = new CriteriaState($this->em->getPersistenceContext());
		$queryPoint = new AdaptiveQueryPoint($this->entityScript->getEntityModel()->createQueryPoint($criteriaState),
				DraftMetaProvider::TABLE_PREFIX, DraftMetaProvider::COLUMN_PREFIX, DraftMetaProvider::ID_COLUMN);
		$criteriaState->addQueryPointToFromClause($queryPoint, 't');
	
		$criteriaState->addSelection(new TranslationCriteriaSelection($criteriaState, $queryPoint,
				$baseEntity, $this->translatables));
	
		return $criteriaState;
	}
	
	public function getLatestDraftByEntityId($entityId) {
		$criteriaState = new CriteriaState(new PersistenceContextImpl($this->em));
		$queryPoint = new AdaptiveQueryPoint($this->entityScript->getEntityModel()->createQueryPoint($criteriaState),
				DraftMetaProvider::TABLE_PREFIX, DraftMetaProvider::COLUMN_PREFIX, DraftMetaProvider::ID_COLUMN);
		
		$criteriaState->addSelection(new DraftCriteriaSelection($this->entityScript, $criteriaState, $queryPoint, $baseEntity));
		$criteriaState->getWhereQueryComparator()->andMatch($queryPoint->registerMetaColumn(DraftMetaProvider::ENTITY_ID_COLUMN),
				QueryComparator::OPERATOR_EQUAL, new QueryConstant($entityId));
		$criteriaState->addOrder($queryPoint->registerMetaColumn(DraftMetaProvider::LAST_MOD_COLUMN), Criteria::ORDER_DIRECTION_DESC);
		$criteriaState->setLimit(1);
		
		$result = $criteriaState->fetch();
		if (sizeof($result)) {
			return current($result);
		}
		return null;
	}
	
	public function getLatestDraftsByEntityId($entityId, Entity $baseEntity) {
		$criteriaState = new CriteriaState(new PersistenceContextImpl($this->em));
		$queryPoint = new AdaptiveQueryPoint($this->entityScript->getEntityModel()->createQueryPoint($criteriaState),
				DraftMetaProvider::TABLE_PREFIX, DraftMetaProvider::COLUMN_PREFIX, DraftMetaProvider::ID_COLUMN);
		
		$criteriaState->addSelection(new DraftCriteriaSelection($this->entityScript, $criteriaState, $queryPoint, $baseEntity));
		$criteriaState->addQueryPointToFromClause($queryPoint, 'd');
		$criteriaState->getWhereQueryComparator()->andMatch($queryPoint->registerMetaColumn(DraftMetaProvider::ENTITY_ID_COLUMN),
				QueryComparator::OPERATOR_EQUAL, new QueryConstant($entityId));
		$lastModColumn = $queryPoint->registerMetaColumn(DraftMetaProvider::LAST_MOD_COLUMN);
		$criteriaState->executeOnBuild(function(SelectStatementBuilder $selectBuilder) use ($lastModColumn) {
			$selectBuilder->addOrderBy($lastModColumn, Criteria::ORDER_DIRECTION_DESC);
		});
		
		return $criteriaState->fetch();
	}
	
	public function getDraftById($id, $entityId, Entity $baseEntity) {
		$criteriaState = new CriteriaState(new PersistenceContextImpl($this->em));
		$queryPoint = new AdaptiveQueryPoint($this->entityScript->getEntityModel()->createQueryPoint($criteriaState),
				DraftMetaProvider::TABLE_PREFIX, DraftMetaProvider::COLUMN_PREFIX, DraftMetaProvider::ID_COLUMN);
		
		$criteriaState->addSelection(new DraftCriteriaSelection($this->entityScript, $criteriaState, $queryPoint, $baseEntity));
		$criteriaState->getWhereQueryComparator()->andMatch($queryPoint->registerMetaColumn(DraftMetaProvider::ENTITY_ID_COLUMN),
				QueryComparator::OPERATOR_EQUAL, new QueryConstant($entityId));
		$criteriaState->getWhereQueryComparator()->andMatch($queryPoint->registerMetaColumn(DraftMetaProvider::ID_COLUMN),
				QueryComparator::OPERATOR_EQUAL, new QueryConstant($draftId));
						
		$result = $criteriaState->fetch();
		if (sizeof($result)) {
			return reset($result);
		}
	
		return $result;
	}
	
	public function createDraft(\DateTime $lastMod, $published, $objectId, Entity $baseEntity) {
		$draft = new Draft(null, $lastMod, $published, $objectId, new \ArrayObject());
		
		$persistingJob = $this->persistDraft($draft, $baseEntity);
		
		$draft->setId($persistingJob->getPersistenceMeta()->getId());
		
		return $draft;
	}
	
	public function saveDraft(Draft $draft) {
		$this->persistDraft($draft);
	}
	
	private function persistDraft(Draft $draft, Entity $baseEntity = null) {
		$draftedEntity = $baseEntity;
		if (null === $draftedEntity) {
			$draftedEntity = $draft->getDraftedEntity(); 
		}
		
		$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($draftedEntity);
		$entityScript = $this->entityScript->determineEntityScript($entityModel);
		
		$ormConfig = $this->em->getDbh()->getMetaData()->getDialect()->getOrmDialectConfig();
		$actionJobMeta = DraftMetaProvider::createAdaptiveActionJobMeta($entityModel->createActionJobMeta());
		$actionJobMeta->setMetaRawValue(DraftMetaProvider::LAST_MOD_COLUMN, $ormConfig->buildDateTimeRawValue($draft->getLastMod()));
		$actionJobMeta->setMetaRawValue(DraftMetaProvider::PUBLISHED_FLAG_COLUMN, (int)$draft->isPublished());
		$actionJobMeta->setMetaRawValue(DraftMetaProvider::ENTITY_ID_COLUMN, OrmUtils::extractId($draftedEntity, $entityModel));
		
		$actionQueue = new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false);
		$persistingJob = new QueuePersistingJob($actionQueue, $actionJobMeta);
		
		$draftId = $draft->getId();
		if (isset($draftId)) {
			$actionJobMeta->setId($draftId);
			$persistingJob->setOldRawDataMap($draft->getDraftedRawDataMap());
		}
		
		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
			if ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled()) {
				$accessProxy = $scriptField->getEntityProperty()->getAccessProxy();
				$value = $accessProxy->getValue($draftedEntity);
				if (isset($baseEntity)) {
					$value = $scriptField->draftCopy($value);
				}
				$scriptField->supplyDraftPersistingJob($value, $persistingJob);
			}
		}
		
		$persistingJob->setInitialized(true);
		$actionQueue->add($persistingJob);
		$actionQueue->execute();

		$draft->setDraftedRawDataMap($persistingJob->getPersistenceMeta()->getRawDataMap());
		$draft->setDraftedObject($draftedEntity);
		
		return $persistingJob;
	}
	
	public function publishDraft(Draft $draft, EntityManager $em, Entity $targetEntity, TranslationModel $targetTranslationModel = null) {
		$draftedEntity = $draft->getDraftedEntity();
		$emm = EntityModelManager::getInstance();
		$draftEntityModel = $emm->getEntityModelByObject($draftedEntity);
		$draftEntityScript = $this->entityScript->determineEntityScript($draftEntityModel);
		$targetEntityModel = $emm->getEntityModelByObject($targetEntity);
		
		$itc = null;
		if (!$draftEntityModel->equals($targetEntityModel)) {
			$itc = new InheritanceTypeChanger($targetEntity, $draftEntityModel->getClass());
			$targetEntity = $itc->getNewObject();
		}
		
		foreach ($draftEntityScript->getFieldCollection()->toArray() as $scriptField) {
			if ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled()) {
				$accessProxy = $scriptField->getEntityProperty()->getAccessProxy();
				$value = $scriptField->publishCopy($accessProxy->getValue($draftedEntity));
				$accessProxy->setValue($targetEntity, $value);
			}
		}
		
		if (isset($itc)) {
			$itc->execute($em);
		} else {
			$em->persist($targetEntity);
		}
		
		if ($this->translationModel === null) return;
		$targetId = OrmUtils::extractId($targetEntity);	
	
		
		foreach ($this->translationModel->getTranslationsByElementId($draft->getId(), $draft->getDraftedEntity()) as $translation) {
			$translatedEntity = $translation->getTranslatedEntity();
			$targetTranslation = $targetTranslationModel->getOrCreateTranslationByLocaleAndElementId($translation->getLocale(), $targetId, $targetEntity);
			$targetTranslatedEntity = $targetTranslation->getTranslatedEntity();
						
			foreach ($draftEntityScript->getFieldCollection()->toArray() as $scriptField) {
				if ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled() 
						&& $scriptField instanceof TranslatableScriptField  && $scriptField->isTranslationEnabled()) {
					$accessProxy = $scriptField->getEntityProperty()->getAccessProxy();
					$value = $scriptField->translationCopy($accessProxy->getValue($translatedEntity));
					$accessProxy->setValue($targetTranslatedEntity, $value);
				}
			}
			
			$targetTranslationModel->saveTranslation($targetTranslation);
		}
	}
	

	
	public function removeDraftsByEntityId($id, Entity $baseEntity) {
		$drafts = $this->getLatestDraftsByEntityId($id, $baseEntity);
		foreach ($drafts as $draft) {
			$this->removeDraft($draft);
		}
	}
	
	public function removeDraft(Draft $draft) {
		$draftedEntity = $draft->getDraftedEntity();
		$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($draftedEntity);
		$entityScript = $this->entityScript->determineEntityScript($entityModel);
		
		$actionJobMeta = DraftMetaProvider::createAdaptiveActionJobMeta($entityModel->createActionJobMeta());
		
		$actionQueue = new RemoveActionQueueImpl($this->em->getPersistenceContext(),
				new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false));
		
		$removingJob = new QueueRemovingJob($actionQueue, $actionJobMeta);
		
		$actionJobMeta->setId($draft->getId());
		$removingJob->setOldRawDataMap($draft->getDraftedRawDataMap());
		
		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
			if ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled()) { 
				$accessProxy = $scriptField->getEntityProperty()->getAccessProxy();
				$value = $accessProxy->getValue($draftedEntity);
					
				$scriptField->supplyDraftRemovingJob($value, $removingJob);
			}
		}
		
		$actionQueue->add($removingJob);
		$actionQueue->execute();
		
		if (isset($this->translationModel)) {
			$this->translationModel->removeTranslationsByElementId($draft->getId(), $draftedEntity);
		}
	}
}



// class DraftModel2 {
// 	private $dataSourceName;
// 	private $em;
// 	private $entityModel;
// 	private $tableName;
// 	private $idColumnName;
// 	private $nameColumnName;
// 	private $lastModColumnName;
// 	private $publishedColumnName;
// 	private $entityIdColumnName;
// 	private $versionableScriptFields = array();
// 	private $columnNames = array();
// 	private $translationModel;
	
// 	public function __construct(EntityManager $em, EntityModel $entityModel, $tableName, $idColumnName, 
// 			$nameColumnName, $lastModColumnName, $publishedColumnName, $entityIdColumnName) {
// 		$this->em = $em;
// 		$this->entityModel = $entityModel;
// 		$this->tableName = $tableName;
// 		$this->idColumnName = $idColumnName;
// 		$this->nameColumnName = $nameColumnName;
// 		$this->lastModColumnName = $lastModColumnName;
// 		$this->publishedColumnName = $publishedColumnName;
// 		$this->entityIdColumnName = $entityIdColumnName;
// 	}
	
// 	public function setTranslationModel(TranslationModel $translationModel = null) {
// 		$this->translationModel = $translationModel;
// 	}
// 	/**
// 	 * @return TranslationModel
// 	 */
// 	public function getTranslationModel() {
// 		return $this->translationModel;
// 	}
// 	/**
// 	 * @return EntityManager
// 	 */
// 	private function getEm() {
// 		return $this->em;
// 	}
// 	/**
// 	 * @return Pdo
// 	 */
// 	private function getDbh() {
// 		return $this->getEm()->getDbh();
// 	}
	
// 	public function putDraftableScriptField(DraftableScriptField $versionableScriptField) {
// 		$this->versionableScriptFields[$versionableScriptField->getId()] = $versionableScriptField;
// 	}
	
// 	public function registerColumnName($orgColumnName, $columnName) { 
// 		$this->columnNames[$orgColumnName] = $columnName;
// 	}
	
// 	private function createSelectStatementBuilder(array $columnNames = array()) {
// 		$selectBuilder = $this->getEm()->getDbh()->getMetaData()
// 				->createSelectStatementBuilder($this->getDbh());
// 		$selectBuilder->addFrom(new QueryTable($this->tableName), null);
// 		foreach ($columnNames as $columnName) {
// 			$selectBuilder->addSelectColumn(new QueryColumn($columnName));
// 		}
// 		return $selectBuilder;
// 	}
	
// 	private function fetchByObjectId(SelectStatementBuilder $selectBuilder, $objectId) {
// 		$selectBuilder->getWhereComparator()->match(new QueryColumn($this->entityIdColumnName),
// 				QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker());
// 		$selectBuilder->addOrderBy(new QueryColumn($this->lastModColumnName), OrderDirection::DESC);
		
// 		$stmt = $this->getDbh()->prepare($selectBuilder->toSqlString());
// 		$stmt->execute(array($objectId));
		
// 		return $stmt->fetchAll(Pdo::FETCH_ASSOC);
// 	}
	
// 	private function fetchById(SelectStatementBuilder $selectBuilder, $id) {
// 		$selectBuilder->getWhereComparator()->match(new QueryColumn($this->idColumnName),
// 				QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker());
		
// 		$stmt = $this->getDbh()->prepare($selectBuilder->toSqlString());
// 		$stmt->execute(array($id));
// 		if ($row = $stmt->fetch(Pdo::FETCH_ASSOC)) {
// 			return $row;
// 		}
// 		return null;
// 	}
	
// 	private function lookupResult($id) {
// 		$selectBuilder = $this->createSelectStatementBuilder();
		
// 		if ($row = $this->fetchById($selectBuilder, $id)) {
// 			return $this->createResult($row);
// 		}
// 	}

// 	public function getDraftsByEntityId($objectId, Entity $baseObject = null) {
// 		$selectBuilder = $this->createSelectStatementBuilder();
	
// 		$drafts = array();
// 		foreach ($this->fetchByObjectId($selectBuilder, $objectId) as $row) {
// 			$drafts[] = $this->createDraftFromRow($row, $baseObject);	
// 		}
// 		return $drafts;
// 	}
	
// 	public function getDraftById($id, Entity $baseEntry = null) {
// 		$selectBuilder = $this->createSelectStatementBuilder();
		
// 		if ($row = $this->fetchById($selectBuilder, $id)) {
// 			return $this->createDraftFromRow($row, $baseEntry = null);
// 		}
		
// 		return null;
// 	}
	
// 	private function createDraftFromRow($row, Entity $baseEntry = null) {
// 		$ormDialectConfig = $this->getDbh()->getMetaData()->getDialect()->getOrmDialectConfig();
		
// 		$draft = new Draft($row[$this->idColumnName], $row[$this->nameColumnName], $ormDialectConfig->parseDateTime($row[$this->lastModColumnName]),
// 				(boolean) $row[$this->publishedColumnName], $row[$this->entityIdColumnName], new \ArrayObject($this->createResult($row)));
		
// 		if (isset($baseEntry)) {
// 			$this->initializeDraftedObject($draft, $baseEntry);
// 		}
		
// 		return $draft;
// 	}
	
// 	public function initializeDraftedObject(Draft $draft, Entity $baseEntry) {
// 		$draft->setDraftedObject($this->createDraftedEntity($draft->getId(), $draft->getDraftedRawDataMap(), $baseEntry));
// 	}
	
// 	private function createResult(array $row) {
// 		$result = array();
// 		foreach ($this->columnNames as $orgColumnName => $columnName) {
// 			$result[$orgColumnName] = $row[$columnName];
// 		}
// 		return $result;
// 	}
	
// 	private function createDraftedEntity($draftId, \ArrayObject $rawDataMap, Entity $baseEntry) {
// 		$draftedEntry = $this->entityModel->copy($baseEntry);
	
// 		$entityStore = $this->em->getEntityManagerState()->getCurrentEntityStore();
// 		$mappingJob = new MappingJob($entityStore);
// 		$mappedValues = new \ArrayObject();

// 		foreach ($this->versionableScriptFields as $versionableScriptField) {
// 			$versionableScriptField->mapDraftValue($draftId, $mappingJob, $rawDataMap, $mappedValues);
// 		}
		
// 		$mappingJob->close();

// 		foreach ($this->versionableScriptFields as $scriptField) {
// 			$entityProperty = $scriptField->getEntityProperty();
// 			$entityProperty->getAccessProxy()->setValue($draftedEntry, $mappedValues[$entityProperty->getName()]);
// 		}
		
// 		return $draftedEntry;
// 	}
	
// 	public function saveDraft(Draft $draft) {
// 		$tx = N2N::createTransaction();
// 		$persistingJob = $this->createPersistingJob($draft->getDraftedObjectId(), $draft->getDraftedEntity(),
// 				$draft->getName(), $draft->getLastMod(), $draft->isPublished());
// 		$persistingJob->getMeta()->setId($draft->getId());
// 		$persistingJob->setOldRawDataMap(new \ArrayObject($this->lookupResult($draft->getId())));
		
// 		$actionQueue = $persistingJob->getActionQueue();
// 		$actionQueue->add($persistingJob);
// 		$actionQueue->execute();
// 		$tx->commit();
// 	}
	
// 	public function publishDraft(Draft $draft, Entity $targetObject) {
// 		$draftedObject = $draft->getDraftedEntity();
// 		foreach ($this->versionableScriptFields as $scriptField) {
// 			$propertyAccessProxy = $scriptField->getAccessProxy()->getPropertyAccessProxy();
// 			$propertyAccessProxy->setValue($targetObject,
// 					$scriptField->draftCopy($propertyAccessProxy->getValue($baseObject)));
// 		}
// 	}
	
// 	public function createDraft($name, \DateTime $lastMod, $published, $objectId, Entity $baseObject) {
// 		$tx = N2N::createTransaction();
// 		$draftedObject = OrmUtils::copy($baseObject);
// 		foreach ($this->versionableScriptFields as $scriptField) {
// 			$propertyAccessProxy = $scriptField->getPropertyAccessProxy();
// 			$propertyAccessProxy->setValue($draftedObject,
// 					$scriptField->draftCopy($propertyAccessProxy->getValue($baseObject)));
// 		}
		
// 		$persistingJob = $this->createPersistingJob($objectId, $draftedObject, 
// 				$name, $lastMod, $published);
		
// 		$actionQueue = $persistingJob->getActionQueue();
// 		$actionQueue->add($persistingJob);
// 		$actionQueue->execute();
		
// 		$tx->commit();
		
// 		return new Draft($persistingJob->getMeta()->getId(), $name, $lastMod, $published, $objectId, $persistingJob->getNewRawDataMap());
// 	}
		
// 	private function createPersistingJob($objectId, Entity $entry, $name, 
// 			\DateTime $lastMod, $published) {
// 		$entityStore = $this->em->getEntityManagerState()->getCurrentEntityStore();
// 		$actionQueue = $entityStore->createActionQueue();
// 		$ormConfig = $this->getDbh()->getMetaData()->getDialect()->getOrmDialectConfig();
		
// 		$meta = new AdaptableActionJobMeta($this->entityModel, $this->tableName, $this->idColumnName, $this->columnNames, $objectId);
// 		$meta->setMetaRawValue($this->nameColumnName, 
// 				mb_substr($name, 0, DraftModelFactory::NAME_COLUMN_LENGTH));
// 		$meta->setMetaRawValue($this->lastModColumnName,
// 				$ormConfig->buildDatetimeRawValue($lastMod));
// 		$meta->setMetaRawValue($this->publishedColumnName, (boolean) $published);
// 		$meta->setMetaRawValue($this->entityIdColumnName, $objectId);
		
// 		$persistingJob = new PersistingJob($actionQueue, $meta);
// 		$persistingJob->setInsertAllowed(true);
// 		$persistingJob->setMergeAllowed(true);
		
// 		foreach ($this->versionableScriptFields as $versionableScriptField) {
// 			$mappedValue = $versionableScriptField->getEntityProperty()->getAccessProxy()->getValue($entry);
// 			$versionableScriptField->supplyDraftPersistingJob($mappedValue, $persistingJob);
// 		}
		
// 		return $persistingJob;
// 	}
		
// 	public function removeDraft(Draft $draft) {
// 		$id = $draft->getId();
// 		if (is_null($id)) return;
		
// 		$tx = N2N::createTransaction();
		
// 		$draftedEntry = $draft->getDraftedEntity();
// 		$entityStore = $this->em->getEntityManagerState()->getCurrentEntityStore();
// 		$actionQueue = $entityStore->createActionQueue();
		
// 		$meta = new AdaptableActionJobMeta($this->entityModel, $this->tableName, $this->idColumnName, $this->columnNames, $draft->getDraftedObjectId());
// 		$meta->setId($id);
		
// 		$deletingJob = new RemovingJob($actionQueue, $meta);
// 		foreach ($this->versionableScriptFields as $versionableScriptField) {
// 			$versionableScriptField->supplyDraftRemovingJob( 
// 					$versionableScriptField->getEntityProperty()->getAccessProxy()->getValue($draftedEntry),
// 					$deletingJob);
// 		}
		
// 		$deletingJob->setDependents(array());
// 		$deletingJob->execute();
		
// 		if (isset($this->translationModel)) {
// 			$translations = $this->translationModel->getTranslationsByElementId($draft->getId(), $draftedEntry);
// 			foreach ($translations as $translation) {
// 				$this->translationModel->removeTranslation($translation);
// 			}
// 		}
		
// 		$tx->commit();
// 	}
	
// 	public function getOrCreateTranslationByDraftAndLocale(Draft $draft, Locale $locale) {
// 		if (is_null($this->translationModel)) return null;
		
// 		return $this->translationModel->getOrCreateTranslationByLocaleAndElementId($locale, 
// 				$draft->getId(), $draft->getDraftedEntity());
// 	}
// }