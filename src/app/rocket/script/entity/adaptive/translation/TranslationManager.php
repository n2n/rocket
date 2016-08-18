<?php
namespace rocket\script\entity\adaptive\translation;

use n2n\l10n\Locale;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\EntityModel;

interface TranslationManager {
	public function getEntityModelManager();
	
	public function getEntityManager();
	
	public function determineElementId(Entity $entity);
	
	public function getManaged(EntityModel $entityModel, $elementId, Locale $locale);
	
	public function getManagedByTranslatedEntity(Entity $translatedEntity);
	
	public function register(Translation $translation, \ArrayObject $translatedRawDataMap);
	
	public function unregister(Translation $translaiton);
	
	public function find(Entity $entity, Locale $locale, $createIfNotFound = false);
	
	public function persist(Translation $translation);
	
	public function createFromEntity(Entity $entity, Locale $locale);
	
	public function clear();
}