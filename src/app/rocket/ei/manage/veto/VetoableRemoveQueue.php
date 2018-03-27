<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\veto;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\LifecycleListener;
use n2n\persistence\orm\LifecycleEvent;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\Spec;
use rocket\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\manage\LiveEiObject;
use rocket\core\model\launch\TransactionApproveAttempt;
use rocket\ei\manage\draft\DraftManager;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\core\container\N2nContext;

class VetoableRemoveQueue implements LifecycleListener {
	private $spec;
	private $em;
	private $draftActions = array();
	private $liveActions = array();
	private $unmangedRemovedEntityObjs = array();
	private $uninitializedActions = array();
	
	public function __construct(Spec $spec) {
		$this->spec = $spec;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function removeEiObject(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}

		$eiType = $eiObject->getEiEntityObj()->getEiType();
		$nss = $eiType->getNestedSetStrategy();
		if (null === $nss) {
			$this->em->remove($eiObject->getEiEntityObj()->getEntityObj());
		} else {
			$nsu = new NestedSetUtils($this->em, $eiType->getEntityModel()->getClass(), $nss); 
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
	 * @param object $entityObj
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
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\core\model\launch\TransactionApproveAttempt
	 */
	public function approve(N2nContext $n2nContext) {
		$this->em->flush();
		
		while (null !== ($action = array_pop($this->uninitializedActions))) {
			$action->getEiObject()->getEiEntityObj()->getEiType()->onRemove($action, $n2nContext);
				
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
		if (!$this->spec->containsEiTypeClass($entityModel->getClass())) {
			$this->unmangedRemovedEntityObjs[spl_object_hash($entityObj)] = $entityObj;
			return;
		}
		
		$eiType = $this->spec->getEiTypeByClass($entityModel->getClass());
		$this->createAction(LiveEiObject::create($eiType, $entityObj));
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
