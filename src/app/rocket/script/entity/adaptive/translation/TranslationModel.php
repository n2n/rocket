<?php
namespace rocket\script\entity\adaptive\translation;

use rocket\script\entity\adaptive\translation\Translation;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\orm\Entity;
use n2n\l10n\Locale;
use n2n\persistence\orm\criteria\CriteriaState;
use rocket\script\entity\adaptive\AdaptiveQueryPoint;
use n2n\persistence\orm\store\QueuePersistingJob;
use n2n\persistence\orm\store\QueueRemovingJob;
use n2n\persistence\orm\store\ActionJobMeta;
use n2n\persistence\orm\EntityModel;

class TranslationModel {
	private $translationManager;
	private $em;
	private $entityModel;
	private $mappingDefinition;
	private $translatables;
	
	public function __construct(TranslationManager $translationManager, EntityModel $entityModel, array $translatables) {
		$this->translationManager = $translationManager;
		$this->entityModel = $entityModel;
		$this->translatables = $translatables;
	}
	
	public function getEntityModel() {
		return $this->entityModel;
	}
	
	public function getTranslationManager() {
		return $this->translationManager;
	}
	
	private function createCriteriaState(Entity $baseEntity) {
		$criteriaState = new CriteriaState($this->translationManager->getEntityManager()->getPersistenceContext());
		$queryPoint = new AdaptiveQueryPoint($this->entityModel->createQueryPoint($criteriaState),
				TranslationMetaProvider::TABLE_PREFIX, TranslationMetaProvider::COLUMN_PREFIX, TranslationMetaProvider::ID_COLUMN);
		$criteriaState->addQueryPointToFromClause($queryPoint, 't');
		
		$criteriaState->addSelection(new TranslationCriteriaSelection($criteriaState, $queryPoint, 
				$baseEntity, $this));
		
		return $criteriaState;
	}
	
	public function getTranslatables() {
		return $this->translatables;
	}
	
	public function lookupTranslationByLocale(Entity $baseEntity, Locale $locale) {
		$elementId = $this->translationManager->determineElementId($baseEntity);
		
		$criteriaState = $this->createCriteriaState($baseEntity);
		$queryPoint = $criteriaState->getQueryPointByAlias('t');
		$criteriaState->getWhereQueryComparator()
				->andMatch($queryPoint->registerMetaColumn(TranslationMetaProvider::LOCALE_COLUMN),
						QueryComparator::OPERATOR_EQUAL, 
						new QueryPlaceMarker($criteriaState->registerPlaceholderValue($locale->getId())))
				->andMatch($queryPoint->registerMetaColumn(TranslationMetaProvider::ELEMENT_ID_COLUMN),
						QueryComparator::OPERATOR_EQUAL, 
						new QueryPlaceMarker($criteriaState->registerPlaceholderValue($elementId)));

		$result = $criteriaState->fetch();
		if (sizeof($result)) {
			return current($result);
		}
		
		return null;
	}
		
	public function lookupTranslations(Entity $baseEntity) {
		$elementId = $this->translationManager->determineElementId($baseEntity);
		$criteriaState = $this->createCriteriaState($baseEntity);
		$queryPoint = $criteriaState->getQueryPointByAlias('t');
		$criteriaState->getWhereQueryComparator()
				->andMatch($queryPoint->registerMetaColumn(TranslationMetaProvider::ELEMENT_ID_COLUMN),
						QueryComparator::OPERATOR_EQUAL,
						new QueryPlaceMarker($criteriaState->registerPlaceholderValue($elementId)));
		
		return $criteriaState->fetch();
	}	
	
	public function createTranslationPersistingJob(Translation $translation, 
			TranslationPersistingActionQueue $translationPersistenceActionQueue, ActionJobMeta $actionJobMeta) {

		$actionJobMeta = TranslationMetaProvider::createAdaptiveActionJobMeta($actionJobMeta);
		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::LOCALE_COLUMN, $translation->getLocale()->getId());
		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::ELEMENT_ID_COLUMN, $translation->getElementId());
		$persistingJob = new QueuePersistingJob($translationPersistenceActionQueue->getPersistenceActionQueue(), $actionJobMeta);
		
		$translationId = $translation->getId();
		if ($translationId !== null) {
			$actionJobMeta->setId($translationId);
			$persistingJob->setOldRawDataMap($this->translationManager->getManagedRawDataMap($translation));
		} else {
			$persistingJob->executeAtEnd(function () use ($persistingJob, $translation) {
				$translation->setId($persistingJob->getPersistenceMeta()->getId());
			});
		}
		
		$translatedEntity = $translation->getTranslatedEntity();
		foreach ($this->getTranslatables() as $translatable) {
			$mappedValue = $translatable->getTranslationEntityProperty()->getAccessProxy()->getValue($translatedEntity);
				
			$translatable->supplyTranslationPersistingJob($mappedValue, 
					$persistingJob, $translationPersistenceActionQueue);
		}
		
		$persistingJob->setInitialized(true);
		
		return $persistingJob;
	}
	
	public function createTranslationRemovingJob(Translation $translation, TranslationRemovingActionQueue $translationRemoveActionQueue, 
			ActionJobMeta $actionJobMeta) {

		$actionJobMeta = TranslationMetaProvider::createAdaptiveActionJobMeta($this->entityModel->createActionJobMeta());
		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::LOCALE_COLUMN, $translation->getLocale()->getId());
		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::ELEMENT_ID_COLUMN, $translation->getElementId());
		
		$removingJob = new QueueRemovingJob($translationRemoveActionQueue->getRemoveActionQueue(), $actionJobMeta);
		
		$actionJobMeta->setId($translation->getId());
		$removingJob->setOldRawDataMap($this->translationManager->getManagedRawDataMap($translation));

		$translatedEntity = $translation->getTranslatedEntity();
		foreach ($this->translatables as $translatable) {
			$value = $translatable->getEntityProperty()->getAccessProxy()->getValue($translatedEntity);
		
			$translatable->supplyTranslationRemovingJob($value, $removingJob, $translationRemoveActionQueue);
		}
		
		return $removingJob;
	}
}



// 	private function isScriptFieldAvailable(ScriptField $scriptField) {
// 		return $scriptField instanceof TranslatableScriptField && $scriptField->isTranslationEnabled()
// 				&& (!$this->draftableOnly
// 						|| ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled()));
// 	}


// 	public function removeTranslationsByElementId($id, Entity $baseEntity) {
// 		$translations = $this->getTranslationsByElementId($id, $baseEntity);
// 		foreach ($translations as $translation) {
// 			$this->removeTranslation($translation);
// 		}
// 	}

// 	public function removeTranslation(Translation $translation) {
// 		$translatedEntity = $translation->getTranslatedEntity();
// 		$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($translatedEntity);
// 		$entityScript = $this->entityScript->determineEntityScript($entityModel);

// 		// 		$ormConfig = $this->em->getDbh()->getMetaData()->getDialect()->getOrmDialectConfig();
// 		$actionJobMeta = TranslationMetaProvider::createAdaptiveActionJobMeta($entityModel->createActionJobMeta());
// // 		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::LOCALE_COLUMN, $translation->getLocale()->getId());
// // 		$actionJobMeta->setMetaRawValue(TranslationMetaProvider::ELEMENT_ID_COLUMN, $translation->getElementId());

// 		$actionQueue = new RemoveActionQueueImpl($this->em->getPersistenceContext(),
// 				new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false));

// 		$removingJob = new QueueRemovingJob($actionQueue, $meta);

// 		$actionJobMeta->setId($translation->getId());
// 		$removingJob->setOldRawDataMap($translation->getTranslatedRawDataMap());

// 		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
// 			if (!$this->isScriptFieldAvailable($scriptField)) continue;

// 			$accessProxy = $scriptField->getEntityProperty()->getAccessProxy();
// 			$value = $accessProxy->getValue($translatedEntity);

// 			$scriptField->supplyTranslationRemovingJob($value, $removingJob);
// 		}

// 		$actionQueue->add($removingJob);
// 		$actionQueue->execute();
// 	}