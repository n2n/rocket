<?php

namespace rocket\script\entity\adaptive\translation;

use n2n\persistence\orm\store\PersistenceActionQueue;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\store\ActionQueue;
class TranslationPersistingActionQueue implements ActionQueue {
	private $persistenceActionQueue;
	private $translationManager;
	
	public function __construct(PersistenceActionQueue $persistenceActionQueue, TranslationManager $translationManager) {
		$this->persistenceActionQueue = $persistenceActionQueue;
		$this->translationManager = $translationManager;
	}
	
	public function getPersistenceActionQueue() {
		return $this->persistenceActionQueue;
	}
	
	public function getTranslationManager() {
		return $this->translationManager;
	}
	
	public function initialize(Translation $translation) {
		$this->createTranslationPersistingJob($translation);
	}
		
	private function createTranslationPersistingJob(Translation $translation) {
		$translatedEntity = $translation->getTranslatedEntity();
		$entityModel = $this->translationManager->getEntityModelManager()->getEntityModelByObject($translatedEntity);
		
		$persistingJob = $this->translationManager->getOrCreateTranslationModel($entityModel)
				->createTranslationPersistingJob($translation, $this, $entityModel->createActionJobMeta());
				
		$this->persistingJobs[spl_object_hash($translatedEntity)] = $persistingJob;
		$this->persistenceActionQueue->add($persistingJob);
		
		return $persistingJob;
	}
	
	public function getOrCreateTranslationPersistingJob(Entity $translatedEntity) {
		$objHash = spl_object_hash($translatedEntity);
		
		if (isset($this->persistingJobs[$objHash])) {
			return $this->persistingJobs[$objHash];
		}
		
		$translation = $this->translationManager->getManagedByTranslatedEntity($translatedEntity);
		return $this->createTranslationPersistingJob($translation);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::getPersistenceContext()
	 */
	public function getPersistenceContext() {
		return $this->persistenceActionQueue->getPersistenceContext();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::getDbh()
	 */
	public function getDbh() {
		return $this->persistenceActionQueue->getDbh();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::add()
	 */
	public function add(\n2n\persistence\orm\store\ActionJob $actionJob) {
		$this->persistenceActionQueue->add($actionJob);
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\ActionQueue::execute()
	 */
	public function execute() {
		$this->persistenceActionQueue->execute();
		
	}


}