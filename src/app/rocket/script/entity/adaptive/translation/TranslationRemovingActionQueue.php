<?php

namespace rocket\script\entity\adaptive\translation;

use n2n\persistence\orm\store\RemoveActionQueue;
use n2n\persistence\orm\store\ActionQueue;
use n2n\persistence\orm\Entity;

class TranslationRemovingActionQueue implements ActionQueue {
	private $removeActionQueue;
	private $translationManager;
	
	public function __construct(RemoveActionQueue $removeActionQueue, TranslationManager $translationManager) {
		$this->removeActionQueue = $removeActionQueue;
		$this->translationManager = $translationManager;
	}

	public function getRemoveActionQueue() {
		return $this->removeActionQueue;
	}
	
	public function getTranslationManager() {
		return $this->translationManager;
	}
	
	public function initialize(Translation $translation) {
		$this->createTranslationRemovingJob($translation);
	}

	private function createTranslationRemovingJob(Translation $translation) {
		$translatedEntity = $translation->getTranslatedEntity();
		$entityModel = $this->translationManager->getEntityModelManager()->getEntityModelByObject($translatedEntity);
		$removingJob = $this->translationManager->getOrCreateTranslationModel($entityModel)->createTranslationRemovingJob(
				$translation, $this, $entityModel->createActionJobMeta());
		
		$this->removingJobs[spl_object_hash($translatedEntity)] = $removingJob;
		$this->removeActionQueue->add($removingJob);	
	}
	
	public function getOrCreateTranslationRemovingJob(Entity $translatedEntity) {
		$objHash = spl_object_hash($translatedEntity);
		
		if (isset($this->removingJobs[$objHash])) {
			return $this->removingJobs[$objHash];
		}
		
		$translation = $this->translationManager->getManagedByTranslatedEntity($translatedEntity);
		if ($translation->getId() === null) return null;
		return $this->createTranslationRemovingJob($translation);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::getPersistenceContext()
	 */
	public function getPersistenceContext() {
		return $this->removeActionQueue->getPersistenceContext();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::getDbh()
	 */
	public function getDbh() {
		return $this->removeActionQueue->getDbh();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::add()
	 */
	public function add(\n2n\persistence\orm\store\ActionJob $actionJob) {
		$this->removeActionQueue->add($actionJob);
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::execute()
	 */
	public function execute() {
		$this->removeActionQueue->execute();
	}
}