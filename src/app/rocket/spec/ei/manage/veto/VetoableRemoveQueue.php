<?php

namespace rocket\spec\ei\manage\veto;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\LifecycleListener;
use n2n\persistence\orm\LifecycleEvent;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\config\SpecManager;
use rocket\spec\ei\manage\EiSelection;
use n2n\util\ex\NotYetImplementedException;

class VetoableRemoveQueue implements LifecycleListener {
	private $em;
	private $specManager;
	private $vetoableRemoveActions = array();
	
	public function __construct(EntityManager $em, SpecManager $specManager) {
		$this->em = $em;
		$this->specManager = $specManager;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function removeEiSelection(EiSelection $eiSelection) {
		if ($eiSelection->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		$this->em->remove($eiSelection->getLiveEntry()->getEntityObj());
	}
	
	public function approve() {
		$this->em->getActionQueue()->registerLifecycleListener($this);
		
		$persistenceContext = $this->em->getPersistenceContext();
		foreach ($persistenceContext->getRemovedEntityObjs() as $entityObj) {
			$this->prepare($persistenceContext->getEntityModelByEntityObj($entityObj), $entityObj);
		}
	}
	
	public function onLifecycleEvent(LifecycleEvent $e, EntityManager $em) {
		$this->prepare($e->getEntityModel(), $e->getEntityObj()); 
	}
	
	private function prepare(EntityModel $entityModel, $entityObj) {
		if (!$this->specManager->containsEiSpecClass($entityModel->getClass())) {
			return;
		}
		
		$this->vetoableRemoveActions[] = new VetoableRemoveAction();
	}
}

class VetoableRemoveAction {
	
}

