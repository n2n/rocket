<?php
namespace rocket\spec\ei\manage\veto;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\LifecycleListener;
use n2n\persistence\orm\LifecycleEvent;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\config\SpecManager;
use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\manage\LiveEiObject;
use rocket\core\model\TransactionApproveAttempt;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\core\container\N2nContext;

class VetoableRemoveQueue implements LifecycleListener {
	private $specManager;
	private $em;
	private $draftActions = array();
	private $liveActions = array();
	private $unmangedRemovedEntityObjs = array();
	private $uninitializedActions = array();
	
	public function __construct(SpecManager $specManager) {
		$this->specManager = $specManager;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function removeEiObject(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}

		$eiSpec = $eiObject->getEiEntityObj()->getEiSpec();
		$nss = $eiSpec->getNestedSetStrategy();
		if (null === $nss) {
			$this->em->remove($eiObject->getEiEntityObj()->getEntityObj());
		} else {
			$nsu = new NestedSetUtils($this->em, $eiSpec->getEntityModel()->getClass(), $nss); 
			$nsu->remove($eiObject->getLiveObject());
		}
		
		$this->createAction($eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function containsEiObject(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		return $this->containsEntityObj($eiObject->getEiEntityObj()->getEntityObj());
	}
	
	/**
	 * @param unknown $entityObj
	 * @return boolean
	 */
	public function containsEntityObj($entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		if (isset($this->liveActions[$objHash])) {
			return true;
		}
		
		return isset($this->unmangedRemovedEntityObjs[$objHash]);
	}
	
	public function initialize(EntityManager $em, DraftManager $draftManager) {
		$this->em = $em;
		
		$persistenceContext = $this->em->getPersistenceContext();
		foreach ($persistenceContext->getRemovedEntityObjs() as $entityObj) {
			$this->prepare($persistenceContext->getEntityModelByEntityObj($entityObj), $entityObj);
		}
		
		$this->em->getActionQueue()->registerLifecycleListener($this);
	}
	
	public function approve(N2nContext $n2nContext) {
		$this->em->flush();
		
		while (null !== ($action = array_pop($this->uninitializedActions))) {
			$action->getEiObject()->getEiEntityObj()->getEiSpec()->onRemove($action, $n2nContext);
				
			if (!$action->hasVeto()) {
				$action->approve();
			}
		}
		
		$reasonMessages = array();
		foreach ($this->liveActions as $liveAction) {
			if (!$liveAction->hasVeto()) continue;
			
			$reasonMessages[] = $liveAction->getReasonMessage();
		}
		
		return new TransactionApproveAttempt($reasonMessages);
	}
	
	public function onLifecycleEvent(LifecycleEvent $e, EntityManager $em) {
		switch ($e->getType()) {
			case LifecycleEvent::PRE_REMOVE:
				$this->prepare($e->getEntityModel(), $e->getEntityObj());
				break;
			case LifecycleEvent::PRE_PERSIST: {
				$this->unprepare($e->getEntityModel(), $e->getEntityObj());
			}
		}
	}
	
	private function prepare(EntityModel $entityModel, $entityObj) {
		if (!$this->specManager->containsEiSpecClass($entityModel->getClass())) {
			$this->unmangedRemovedEntityObjs[spl_object_hash($entityObj)] = $entityObj;
			return;
		}
		
		$eiSpec = $this->specManager->getEiSpecByClass($entityModel->getClass());
		$this->createAction(LiveEiObject::create($eiSpec, $entityObj));
	}
	
	private function unprepare($entityObj) {
		$objHash = spl_object_hash($entityObj);
		if (!isset($this->liveActions[$objHash])) return;
			
		$action = $this->liveActions[$objHash];
		unset($this->liveActions[$objHash]);
		unset($this->uninitializedActions[spl_object_hash($action)]);
	}
	
	private function createAction(EiObject $eiObject) {
		if ($this->containsEiObject($eiObject)) return;
		
		$action = new VetoableRemoveAction($eiObject, $this);
		
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		} else {
			 $this->liveActions[spl_object_hash($eiObject->getEiEntityObj()->getEntityObj())] = $action;
		}
		
		$this->uninitializedActions[spl_object_hash($action)] = $action;
	}
}
