<?php

namespace rocket\script\entity\adaptive\draft;

use n2n\persistence\orm\criteria\CriteriaSelection;
use n2n\persistence\orm\criteria\CriteriaState;
use rocket\script\entity\adaptive\AdaptiveQueryPoint;
use n2n\persistence\orm\Entity;
use rocket\script\entity\manage\PrefixMetaGenerator;
use n2n\persistence\orm\criteria\CriteriaSelectionBuilder;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\store\MappingJob;
use n2n\reflection\ReflectionContext;

class DraftCriteriaSelection implements CriteriaSelection {
	private $criteriaState;
	private $queryPoint;
	private $baseEntity;
	private $csb;
	private $draftables;
	private $metaQueryItems = array();
	private $metaAliases = array();
	
	public function __construct(CriteriaState $criteriaState, AdaptiveQueryPoint $queryPoint, Entity $baseEntity, DraftModel $draftModel) {
		$this->criteriaState = $criteriaState;
		$this->queryPoint = $queryPoint;
		$this->baseEntity = $baseEntity;
		$this->csb = new CriteriaSelectionBuilder($criteriaState, $queryPoint);
		$this->draftModel = $draftModel;
				
		$queryPoint->setMetaGenerator(new PrefixMetaGenerator(DraftMetaProvider::TABLE_PREFIX, DraftMetaProvider::COLUMN_PREFIX));
		$queryPoint->makeIdentifiable();
		
		$queryPoint->setIdColumnName(DraftMetaProvider::ID_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::ID_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::ID_COLUMN);
		$this->metaAliases[DraftMetaProvider::ID_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::ID_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::LAST_MOD_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::LAST_MOD_COLUMN);
		$this->metaAliases[DraftMetaProvider::LAST_MOD_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::LAST_MOD_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::LAST_MOD_BY_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::LAST_MOD_BY_COLUMN);
		$this->metaAliases[DraftMetaProvider::LAST_MOD_BY_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::LAST_MOD_BY_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::ENTITY_ID_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::ENTITY_ID_COLUMN);
		$this->metaAliases[DraftMetaProvider::ENTITY_ID_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::ENTITY_ID_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::PUBLISHED_FLAG_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::PUBLISHED_FLAG_COLUMN);
		$this->metaAliases[DraftMetaProvider::PUBLISHED_FLAG_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::PUBLISHED_FLAG_COLUMN);
		$this->metaQueryItems[DraftMetaProvider::NAVIGATABLE_COLUMN] = $queryPoint->registerMetaColumn(DraftMetaProvider::NAVIGATABLE_COLUMN);
		$this->metaAliases[DraftMetaProvider::NAVIGATABLE_COLUMN] = $criteriaState->createColumnAlias(DraftMetaProvider::NAVIGATABLE_COLUMN);

		foreach ($this->draftModel->getAllTranslatables() as $translatable) {
			if (null !== $transltable->getTranslationColumnName()) {
				$this->csb->registerProperty($transltable->getTranslationEntityProperty());
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
		$ormDialectConfig = $this->criteriaState->getEntityManager()->getDbh()->getMetaData()->getDialect()->getOrmDialectConfig();
		
		$entityModel = $this->queryPoint->identifyEntityModel($result);
		$draftedRawDataMap = $this->csb->createRawDataMap($entityModel, $result);
		
		$draftModel = $this->draftManager->getOrCreateDraftModel($entityModel);
				
		$draft = new Draft($result[$this->metaAliases[DraftMetaProvider::ID_COLUMN]],
				$ormDialectConfig->parseDateTime($result[$this->metaAliases[DraftMetaProvider::LAST_MOD_COLUMN]]),
				$result[$this->metaAliases[DraftMetaProvider::LAST_MOD_BY_COLUMN]],
				(boolean)$result[$this->metaAliases[DraftMetaProvider::PUBLISHED_FLAG_COLUMN]],
				$result[$this->metaAliases[DraftMetaProvider::ENTITY_ID_COLUMN]],
				$result[$this->metaAliases[DraftMetaProvider::NAVIGATABLE_COLUMN]], $draftedRawDataMap);
		
		$lowestCommonEntityModel = OrmUtils::findLowestCommonEntityModel($entityModel, $this->draftModel->getEntityModel());
		$draftedEntity = ReflectionContext::createObject($entityModel->getClass());
		$lowestCommonEntityModel->copy($this->baseEntity, $draftedEntity);		
					
		$draftMappingJob = new DraftMappingJob($mappingJob, $this->draftManager,
				$draft->getId(), $draft->getDraftedObjectId(), $this->baseEntity);
		
		$mappedValues = new \ArrayObject();
		foreach ($draftModel->getDraftables() as $draftable) {
			$draftable->mapDraftValue($draftMappingJob, $draft->getDraftedRawDataMap(), $mappedValues);
		}

		$draftMappingJob->mapEntityValues($entityModel, $draftedEntity, $mappedValues);
		$draft->setDraftedObject($draftedEntity);
		
		return $draft;
	}
}