<?php
namespace rocket\script\entity;

use rocket\script\core\ScriptManager;
use n2n\persistence\orm\EntityManager;
use rocket\script\entity\adaptive\translation\TranslationManager;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\OrmUtils;
use rocket\script\entity\adaptive\translation\TranslationRemovingActionQueue;
use rocket\script\entity\adaptive\translation\Translation;
use n2n\persistence\orm\store\PersistenceActionQueueImpl;
use n2n\persistence\orm\store\RemoveActionQueueImpl;
use rocket\script\entity\adaptive\translation\TranslationPersistingActionQueue;
use n2n\persistence\orm\EntityModel;
use n2n\l10n\Locale;
use rocket\script\entity\adaptive\draft\DraftManager;
use n2n\reflection\IllegalArgumentException;

class ScriptTranslationManager implements TranslationManager {
	private $scriptManager;
	private $em;
	private $translationModels = array();
	private $entityModelManager;
	private $draftManager;
	
	private $managedTranslations = array();
	private $managedRawDataMaps = array();
	private $managedTranslationGroups = array();
		
	public function __construct(ScriptManager $scriptManager, EntityManager $em, DraftManager $draftManager = null) {
		$this->scriptManager = $scriptManager;
		$this->em = $em;
		$this->entityModelManager = $scriptManager->getEntityModelManager();
		$this->draftManager = $draftManager;
	}
	
	public function clear() {
		$this->managedTranslations = array();
		$this->managedRawDataMaps = array();
		$this->managedTranslationGroups = array();
	}
	
	public function getEntityModelManager() {
		return $this->entityModelManager;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function getOrCreateTranslationModel(EntityModel $entityModel) {
		$class = $entityModel->getClass();
		if (isset($this->translationModels[$class->getName()])) {
			return $this->translationModels[$class->getName()];
		}
				
		return $this->translationModels[$class->getName()] = $this->scriptManager
				->getEntityScriptByClass($class)->createTranslationModel($this, $this->draftManager !== null);
	}
	
	public function register(Translation $translation, \ArrayObject $rawDataMap = null) {
		$objHash = spl_object_hash($translation->getTranslatedEntity());
		$className = $this->entityModelManager->getEntityModelByObject($translation->getTranslatedEntity())->getClass()->getName();
		$elementId = $translation->getElementId();
		$localeId = $translation->getLocale()->getId();
		
		if (isset($this->managedTranslations[$className][$elementId][$localeId])) {
			throw new IllegalArgumentException('Translation already managed.');
		}
		
		if (!isset($this->managedTranslationGroups[$className])) {
			$this->managedTranslationGroups[$className] = array();
		}
		
		if (!isset($this->managedTranslationGroups[$className][$elementId])) {
			$this->managedTranslationGroups[$className][$elementId] = array();
		} 
		
		$this->managedTranslations[$objHash] = $translation;
		if ($rawDataMap !== null) {
			$this->managedRawDataMaps[$objHash] = $rawDataMap;
		}
		$this->managedTranslationGroups[$className][$elementId][$localeId] = $translation;
	}
	
	public function unregister(Translation $translation) {
		$objHash = spl_object_hash($translation->getTranslatedEntity());
		$className = $this->entityModelManager->getEntityModelByObject($entity)->getClass()->getName();
		$elementId = $translation->getElementId();
		$localeId = $translation->getLocale()->getId();
		
		unset($this->managedTranslations[$objHash]);
		unset($this->managedRawDataMaps[$objHash]);
		unset($this->managedTranslationGroups[$className][$elementId][$localeId]);
	}
		
	public function getManaged(EntityModel $entityModel, $elementId, Locale $locale) {
		if (isset($this->managedTranslationGroups[$entityModel->getClass()->getName()][$elementId][$locale->getId()])) {
			return $this->managedTranslationGroups[$entityModel->getClass()->getName()][$elementId][$locale->getId()];
		}
		
		return null;
	}
	
	public function getManagedByTranslatedEntity(Entity $translatedEntity) {
		$objHash = spl_object_hash($translatedEntity);
		if (isset($this->managedTranslations[$objHash])) {
			return $this->managedTranslations[$objHash];
		}
		
		throw new IllegalArgumentException('Passed translated entity not managed');
	}
	
	public function isManaged(Translation $translation) {
		return isset($this->managedTranslations[spl_object_hash($translation->getTranslatedEntity())]);
	}
	
	public function getManagedRawDataMap(Translation $translation) {
		$objHash = spl_object_hash($translation->getTranslatedEntity());
		if (isset($this->managedRawDataMaps[$objHash])) {
			return $this->managedRawDataMaps[$objHash];
		}
		
		throw new IllegalArgumentException('No RawDataMap available for passed translation.');
	}
	
	public function find(Entity $entity, Locale $locale, $createIfNotFound = false) {
		$elementId = $this->determineElementId($entity);
		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
		
		if (null !== ($translation = $this->getManaged($entityModel, $elementId, $locale))) {
			return $translation;
		}
		
		$translationModel = $this->getOrCreateTranslationModel($entityModel);
		
		if (null !== ($translation = $translationModel->lookupTranslationByLocale($entity, $locale))) {
			return $translation;
		}
		
		if ($createIfNotFound) {
			return $this->createFromEntity($entity, $locale);
		}
		
		return null;
	}
	
	public function findAll(Entity $entity) {
		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
		$translationModel = $this->getOrCreateTranslationModel($entityModel);
		
		return $translationModel->lookupTranslations($entity);
	}

	public function createFromEntity(Entity $entity, Locale $locale) {
		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
		$elementId = $this->determineElementId($entity);
		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
		$entityScript = $this->scriptManager->getEntityScriptByClass($entityModel->getClass());
	
		$translation = new Translation(null, $locale, $elementId, $entityModel->copy($entity));
	
		$mappingDefinition = $entityScript->createMappingDefinition();
		$mappingDefinition->translationWriteAll($translation,
				$mappingDefinition->translationCopyAll(
						$mappingDefinition->readAll($entity), $locale, $this, false));
		
		$this->register($translation, null);
	
		return $translation;
	}
	
	public function persist(Translation $translation) {
		$translationModel = $this->getOrCreateTranslationModel(
				$this->entityModelManager->getEntityModelByObject($translation->getTranslatedEntity()));
		
		$actionQueue = new TranslationPersistingActionQueue(new PersistenceActionQueueImpl(
				$this->em->getPersistenceContext(), false), $this);
		$actionQueue->initialize($translation);
		$this->em->getPersistenceContext()->addBufferedActionQueue($actionQueue);
	}
	
	public function remove(Translation $translation) {
		$actionQueue = new TranslationRemovingActionQueue(new RemoveActionQueueImpl(
				$this->em->getPersistenceContext(), 
				new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false)), $this);
		$actionQueue->initialize($translation);
		$this->em->getPersistenceContext()->addBufferedActionQueue($actionQueue);
	}
	
	public function determineElementId(Entity $entity) {
		if ($this->draftManager === null) {
			$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
			return OrmUtils::extractId($entity, $entityModel);
		}
		
		return $this->draftManager->getDraftByDraftedEntity($baseEntity)->getId();
	}
	
	public function saveTranslation(Translation $translation) {
		$this->em->getPersistenceContext()->addBufferedActionQueue(
				new TranslationPersistingActionQueue(new PersistenceActionQueueImpl(
						$this->em->getPersistenceContext(), false), $this));
	}
	
	public function removeTranslation(Translation $translation) {
		$persistingActionQueue = new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false);
		$removingActionQueue = new RemoveActionQueueImpl($this->em->getPersistenceContext(), $persistingActionQueue);
		$this->em->getPersistenceContext()->addBufferedActionQueue(new TranslationRemovingActionQueue($removeActionQueue, $this));
		// @todo remove from $this->translations
	}
}	