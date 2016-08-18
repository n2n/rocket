<?php

namespace rocket\script\entity;

use rocket\script\core\ScriptManager;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\EntityModelManager;
use rocket\script\entity\field\DraftableScriptField;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\store\PersistenceActionQueueImpl;
use n2n\persistence\orm\store\RemoveActionQueueImpl;
use n2n\persistence\orm\EntityModel;
use rocket\script\entity\adaptive\draft\DraftManager;

class ScriptDraftManager implements DraftManager {
	private $scriptManager;
	private $draftModels = array();
	private $entityModelManager;
	private $translationManager;
	private $drafts = array();
	
	public function __construct(ScriptManager $scriptManager, EntityManager $em) {
		$this->scriptManager = $scriptManager;
		$this->em = $em;
		// @todo fixen
		$this->entityModelManager = EntityModelManager::getInstance();
		$this->translationManager = new ScriptTranslationManager($scriptManager, $em, $this);
	}
	
	public function getTranslationManager() {
		return $this->translationManager;
	}
	
	public function getOrCreateDraftModel(EntityModel $entityModel) {
		$class = $entityModel->getEntityModel()->getTopEntityModel();
		if (isset($this->draftModels[$class->getName()])) {
			return $this->draftModels[$class->getName()];
		}
		
		$entityScript = $this->scriptManager->getEntityScriptByClass($class);
		
		$this->draftModels[$class->getName()] = $draftModel = new DraftModel($em);
		
		foreach ($entityScript->getFieldCollection()->combineAll() as $field) {
			if ($field instanceof DraftableScriptField && $field->isDraftEnabled()) {
				$draftModel->addDraftable($field);
			}
		}
		
		foreach ($entityScript->getModificatorCollection()->combineAll() as $constraint) {
			$constraint->setupDraftModel($draftModel, $this->drafManager !== null);
		}

		return $draftModel;
	}
	
	public function containsManagedDraftId(Entity $baseEntity, $id) {
		
	}
	
	public function getManagedDraftById(Entity $baseEntity, $id) {
		
	}
	
	public function findDraftById(Entity $baseEntity, $id) {
		$objectHash = spl_object_hash($baseEntity);
		if (isset($this->drafts[$objectHash][$id])) {
			return $this->drafts[$objectHash][$id];
		}
		
		$entityModel = $this->entityModelManager->getEntityModelByObject($baseEntity);
		$translationModel = $this->getOrCreateDraftModel($entityModel);
		
		return $this->drafts[$objectHash][$locale->getId()] 
				= $translationModel->getOrCreateTranslationByLocaleAndElementId($locale, $elementId, $baseEntity);
	}
	
	public function findLatestDraft(Entity $baseEntity) {
		
	}
	
	public function findDrafts(Entity $baseEntity) {
		
	}
	
// 	public function determineElementId(Entity $baseEntity) {
// 		if ($this->draftManager === null) {
// 			$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
// 			return OrmUtils::extractId($baseEntity, $entityModel);
// 		}
		
// 		return $this->draftManager->getDraftByDraftedEntity($baseEntity)->getId();
// 	}
	
	public function saveDraft(Draft $draft) {
		$this->em->getPersistenceContext()->addBufferedActionQueue(
				new TranslationPersistingActionQueue(new PersistenceActionQueueImpl(
						$this->em->getPersistenceContext(), false), $this));
	}
	
	public function removeDraft(Draft $draft) {
		$persistingActionQueue = new PersistenceActionQueueImpl($this->em->getPersistenceContext(), false);
		$removingActionQueue = new RemoveActionQueueImpl($this->em->getPersistenceContext(), $persistingActionQueue);
		$this->em->getPersistenceContext()->addBufferedActionQueue(new TranslationRemovingActionQueue($removeActionQueue, $this));
	}
}	