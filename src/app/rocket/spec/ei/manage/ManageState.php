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
namespace rocket\spec\ei\manage;

use n2n\web\http\controller\ControllerContext;
use n2n\context\RequestScoped;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\EiFrame;
use rocket\user\model\LoginContext;
use n2n\core\container\N2nContext;
use rocket\user\bo\RocketUser;
use rocket\core\model\Rocket;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\EntityManager;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\EiFrameFactory;
use rocket\spec\ei\manage\draft\DraftManager;
use rocket\spec\ei\security\EiPermissionManager;
use rocket\spec\ei\manage\veto\VetoableRemoveQueue;

class ManageState implements RequestScoped {
	private $n2nContext;
	private $selectedMenuItem;
	private $user;
	private $eiPermissionManager;
	private $eiFrames = array();
	private $entityManager;
	private $draftManager;
	private $vetoableRemoveQueue;
	
	private function _init(N2nContext $n2nContext, LoginContext $loginContext, Rocket $rocket) {
		$this->n2nContext = $n2nContext;
	
		if (null !== ($user = $loginContext->getCurrentUser())) {
			$this->setUser($user);
		}
	}
		
	public function getN2nContext() {
		return $this->n2nContext;
	}
	
	/**
	 * @return RocketUser 
	 */
	public function getUser() {
		return $this->user;
	}
	
	public function setUser(RocketUser $user) {
		$this->user = $user;
	}
	
	public function getEiPermissionManager(): EiPermissionManager {
		if ($this->eiPermissionManager === null) {
			throw new IllegalStateException('No EiPermissionManager assigned.');
		}
		
		return $this->eiPermissionManager;
	}
	
	public function setEiPermissionManager(EiPermissionManager $eiPermissionManager) {
		$this->eiPermissionManager = $eiPermissionManager;
	}
	/**
	 * @throws IllegalStateException
	 * @return EntityManager
	 */
	public function getEntityManager(): EntityManager {
		if ($this->entityManager === null) {
			throw new IllegalStateException('No EntityManager assigned.');
		}
		
		return $this->entityManager;
	}
	
	/**
	 * @param EntityManager $entityManager
	 */
	public function setEntityManager(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	} 
	/**
	 * @throws IllegalStateException
	 * @return DraftManager
	 */
	public function getDraftManager(): DraftManager {
		if ($this->draftManager === null) {
			throw new IllegalStateException('No DraftManager assigned.');
		}
		
		return $this->draftManager;
	}
	
	/**
	 * @param DraftManager $draftManager
	 */
	public function setDraftManager(DraftManager $draftManager) {
		$this->draftManager = $draftManager;
	} 
	
	/**
	 * @return \rocket\spec\ei\manage\veto\VetoableRemoveQueue
	 */
	public function getVetoableRemoveActionQueue() {
		if ($this->vetoableRemoveQueue === null) {
			throw new IllegalStateException('No VetoableRemoveQueue assigned.');
		}
		
		return $this->vetoableRemoveQueue;
	}
	
	public function setVetoableRemoveActionQueue(VetoableRemoveQueue $vetoableRemoveQueue) {
		$this->vetoableRemoveQueue = $vetoableRemoveQueue;
	}
	
	public function createEiFrame(EiMask $contextEiMask, ControllerContext $controllerContext) {
		$eiFrameFactory = new EiFrameFactory($contextEiMask);
		
		$parentEiFrame = null;
		if (sizeof($this->eiFrames)) {
			$parentEiFrame = end($this->eiFrames);
		}
		
		return $this->eiFrames[] = $eiFrameFactory->create($controllerContext, $this, false, $parentEiFrame);
	}
	
	public function isActive(): bool {
		return !empty($this->eiFrames);
	}
	
	/**
	 * 
	 * @param EiSpec $eiSpec
	 * @throws UnsuitableEiFrameException
	 * @return \rocket\spec\ei\manage\EiFrame
	 */
	public function peakEiFrame(EiSpec $eiSpec = null): EiFrame {
		if (!sizeof($this->eiFrames)) {
			throw new ManageException('No active EiFrames found.');
		}  
		
		end($this->eiFrames);
		$eiFrame = current($this->eiFrames);
// 		if (isset($eiSpec) && !$eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->equals($eiSpec)) {
// 			throw new UnsuitableEiFrameException(
// 					'Latest EiFrame is not assigned to passed  (id: ' . $eiSpec->getId() . ').');
// 		}

		return $eiFrame;
	}
	
	public function popEiFrameBy(EiSpec $eiSpec) {
		$this->peakEiFrame($eiSpec);
		return array_pop($this->eiFrames);
	}
	
	public function getMainId() {
		if (!sizeof($this->eiFrames)) {
			return null;
		}
		
		reset($this->eiFrames);
		return current($this->eiFrames)->getId();
	}
}
