<?php

namespace rocket\script\entity\adaptive\translation;

use n2n\persistence\orm\criteria\CriteriaSelection;
use n2n\persistence\orm\criteria\CriteriaState;
use rocket\script\entity\adaptive\AdaptiveQueryPoint;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\criteria\CriteriaSelectionBuilder;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\store\MappingJob;
use n2n\l10n\Locale;
use n2n\reflection\ReflectionContext;
use rocket\script\entity\adaptive\PrefixMetaGenerator;

class TranslationCriteriaSelection implements CriteriaSelection {
	private $criteriaState;
	private $baseEntity;
	private $csb;
	private $queryPoint;
	private $metaQueryItems = array();
	private $metaAliases = array();
	
	public function __construct(CriteriaState $criteriaState, AdaptiveQueryPoint $queryPoint, Entity $baseEntity, 
			TranslationModel $translationModel) {
		$this->criteriaState = $criteriaState;
		$this->queryPoint = $queryPoint;
		$this->baseEntity = $baseEntity;
		$this->csb = new CriteriaSelectionBuilder($criteriaState, $queryPoint);
		$this->translationModel = $translationModel;
		
		$queryPoint->setMetaGenerator(new PrefixMetaGenerator(TranslationMetaProvider::TABLE_PREFIX, TranslationMetaProvider::COLUMN_PREFIX));
		$queryPoint->makeIdentifiable();
		
		$queryPoint->setIdColumnName(TranslationMetaProvider::ID_COLUMN);
		$this->metaQueryItems[TranslationMetaProvider::ID_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::ID_COLUMN);
		$this->metaAliases[TranslationMetaProvider::ID_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::ID_COLUMN);
		$this->metaQueryItems[TranslationMetaProvider::LOCALE_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::LOCALE_COLUMN);
		$this->metaAliases[TranslationMetaProvider::LOCALE_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::LOCALE_COLUMN);
		$this->metaQueryItems[TranslationMetaProvider::ELEMENT_ID_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::ELEMENT_ID_COLUMN);
		$this->metaAliases[TranslationMetaProvider::ELEMENT_ID_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::ELEMENT_ID_COLUMN);
		
		foreach ($translationModel->getTranslatables() as $translatable) {
			if (null !== $translatable->getTranslationColumnName()) {
				$this->csb->registerProperty($translatable->getTranslationEntityProperty());
			}
		}
	}
	
	public function apply(SelectStatementBuilder $selectBuilder) {
		foreach ($this->metaQueryItems as $columnName => $queryItem) {
			$selectBuilder->addSelectColumn($queryItem, $this->metaAliases[$columnName]);
		}
		
		$this->csb->apply($selectBuilder);
	}
	
	public function buildValue(MappingJob $mappingJob, array $result) {
		$entityModel = $this->queryPoint->identifyEntityModel($result);
		$translatedRawDataMap = $this->csb->createRawDataMap($entityModel, $result);
		
		if (!$entityModel->equals($this->translationModel->getEntityModel())) {
			throw new TranslationConflictException();
		}
		
		$id = $result[$this->metaAliases[TranslationMetaProvider::ID_COLUMN]];
		$elementId = $result[$this->metaAliases[TranslationMetaProvider::ELEMENT_ID_COLUMN]];
		$locale = new Locale($result[$this->metaAliases[TranslationMetaProvider::LOCALE_COLUMN]]);
		$translationManager = $this->translationModel->getTranslationManager();
		
		if (null !== ($translation = $translationManager->getManaged($entityModel, $elementId, $locale))) {
			$translationManager->register($translation, $translatedRawDataMap);
			return $translation;
		}
		
		$translatedEntity = ReflectionContext::createObject($entityModel->getClass());
		$translation = new Translation($id, $locale,
				$elementId,
				$translatedEntity);
		$translationManager->register($translation, $translatedRawDataMap);
		
		$entityModel->copy($this->baseEntity, $translatedEntity);	
						
		$translationMappingJob = new TranslationMappingJob($mappingJob, $translationManager, 
				$locale, $this->baseEntity);
		
		$mappedValues = new \ArrayObject();
		foreach ($this->translationModel->getTranslatables() as $translatable) {
			$translatable->mapTranslationValue($translationMappingJob, $translatedRawDataMap, $mappedValues);
		}
		
		$mappingJob->mapEntityValues($entityModel, $translatedEntity, $mappedValues);
			
		return $translation;
	}
}

class TranslationMappingJob {
	public function __construct(MappingJob $mappingJob, TranslationManager $translationManager, Locale $locale, Entity $baseEntity) {
		$this->mappingJob = $mappingJob;
		$this->translationManager = $translationManager;
		$this->locale = $locale;
		$this->baseEntity = $baseEntity;
	}
	
	public function getMappingJob() {
		return $this->mappingJob;
	}
	
	public function getTranslationManager() {
		return $this->translationManager;
	}
	
	public function getLocale() {
		return $this->locale;
	}
	
	public function getBaseEntity() {
		return $this->baseEntity;
	}
}
// class TranslationCriteriaSelection implements CriteriaSelection {
// 	private $entityScript;
// 	private $criteriaState;
// 	private $baseEntity;
// 	private $csb;
// 	private $draftableOnly;
// 	private $queryPoint;
// 	private $metaQueryItems = array();
// 	private $metaAliases = array();
	
// 	public function __construct(EntityScript $entityScript, CriteriaState $criteriaState, AdaptiveQueryPoint $queryPoint, Entity $baseEntity, $draftableOnly) {
// 		$this->entityScript = $entityScript;
// 		$this->criteriaState = $criteriaState;
// 		$this->baseEntity = $baseEntity;
// 		$this->csb = new CriteriaSelectionBuilder($criteriaState, $queryPoint);
// 		$this->draftableOnly = $draftableOnly;
// 		$this->queryPoint = $queryPoint;
		
// 		$queryPoint->setMetaGenerator(new PrefixMetaGenerator(TranslationMetaProvider::TABLE_PREFIX, TranslationMetaProvider::COLUMN_PREFIX));
// 		$queryPoint->makeIdentifiable();
		
// 		$queryPoint->setIdColumnName(TranslationMetaProvider::ID_COLUMN);
// 		$this->metaQueryItems[TranslationMetaProvider::ID_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::ID_COLUMN);
// 		$this->metaAliases[TranslationMetaProvider::ID_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::ID_COLUMN);
// 		$this->metaQueryItems[TranslationMetaProvider::LOCALE_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::LOCALE_COLUMN);
// 		$this->metaAliases[TranslationMetaProvider::LOCALE_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::LOCALE_COLUMN);
// 		$this->metaQueryItems[TranslationMetaProvider::ELEMENT_ID_COLUMN] = $queryPoint->registerMetaColumn(TranslationMetaProvider::ELEMENT_ID_COLUMN);
// 		$this->metaAliases[TranslationMetaProvider::ELEMENT_ID_COLUMN] = $criteriaState->createColumnAlias(TranslationMetaProvider::ELEMENT_ID_COLUMN);
		
// 		foreach ($this->entityScript->getFieldCollection()->combineAll() as $scriptField) {
// 			if ($scriptField instanceof TranslatableScriptField && $scriptField->isTranslationEnabled() 
// 					&& (!$this->draftableOnly || $scriptField instanceof DraftableScriptField)) {
// 				$this->csb->registerProperty($scriptField->getEntityProperty());
// 			}
// 		}
// 	}
	
// 	public function apply(SelectStatementBuilder $selectBuilder) {
// 		foreach ($this->metaQueryItems as $columnName => $queryItem) {
// 			$selectBuilder->addSelectColumn($queryItem, $this->metaAliases[$columnName]);
// 		}
		
// 		 $this->csb->apply($selectBuilder);
// 	}
	
// 	public function buildValue(MappingJob $mappingJob, array $result) {
// 		$entityModel = $this->queryPoint->identifyEntityModel($result);
// 		$entityScript = $this->entityScript->determineEntityScript($entityModel);
// 		$translatedRawDataMap = $this->csb->createRawDataMap($entityModel, $result);
		
// 		if (!$entityModel->equals(EntityModelManager::getInstance()->getEntityModelByObject($this->baseEntity))) {
// 			throw new TranslationConflictException();
// 		}
		
// 		$translation = new Translation($result[$this->metaAliases[TranslationMetaProvider::ID_COLUMN]],
// 				new Locale($result[$this->metaAliases[TranslationMetaProvider::LOCALE_COLUMN]]),
// 				(boolean)$result[$this->metaAliases[TranslationMetaProvider::ELEMENT_ID_COLUMN]], 
// 				$translatedRawDataMap);
		
// 		$translationEntity = ReflectionContext::createObject($entityModel->getClass());
// 		$entityModel->copy($this->baseEntity, $translationEntity);		
						
// 		$mappingJob->executeAtEnd(function(MappingJob $mappingJob) use ($entityScript, $entityModel, $translation, $translationEntity) {
// 			$mappedValues = new \ArrayObject();
// 			foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
// 				if ($scriptField instanceof TranslatableScriptField && $scriptField->isTranslationEnabled() 
// 					&& (!$this->draftableOnly || $scriptField instanceof DraftableScriptField)) {
// 					$scriptField->mapTranslationValue($translation->getId(), $mappingJob, $translation->getTranslatedRawDataMap(), $mappedValues);
// 				}
// 			}
			
// 			$mappingJob->writeObject($entityModel, $translationEntity, $mappedValues);
// 			$translation->setTranslatedEntity($translationEntity);
// 		});
		
// 		return $translation;
// 	}
// }