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
namespace rocket\ei;

use rocket\core\model\launch\LaunchPad;
use rocket\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use n2n\web\http\controller\ControllerContext;
use n2n\web\http\controller\Controller;
use rocket\ei\manage\ManageState;
use n2n\util\type\CastUtils;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use n2n\core\container\PdoPool;
use n2n\util\uri\Path;
use rocket\core\model\launch\TransactionApproveAttempt;
use rocket\ei\manage\veto\EiLifecycleMonitor;

class EiLaunchPad implements LaunchPad {
	private $id;
	private $eiMask;
	private $label;
	
	public function __construct(string $id, EiMask $eiMask, string $label = null) {
		$this->id = $id;
		$this->eiMask = $eiMask;
		$this->label = $label;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\launch\LaunchPad::getId()
	 */
	public function getId(): string {
		return $this->id;
	}

	public function getLabel(): string {
		return $this->label ?? $this->eiMask->getPluralLabelLstr();
	}
	
	public function isAccessible(N2nContext $n2nContext): bool {
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		$overviewEiCommand = $this->eiMask->getEiCommandCollection()->getGenericOverviewEiCommand(true);
		
		return $loginContext->getSecurityManager()->getEiPermissionManager()
				->isEiCommandAccessible($overviewEiCommand, $n2nContext->lookup(ManageState::class));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\launch\LaunchPad::determinePathExt($n2nContext)
	 */
	public function determinePathExt(N2nContext $n2nContext) {
		$overviewEiCommand = $this->eiMask->getEiCommandCollection()
				->getGenericOverviewEiCommand(true);
		
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		if ($loginContext->getSecurityManager()->getEiPermissionManager()
				->isEiCommandAccessible($overviewEiCommand, $n2nContext->lookup(ManageState::class))) {
			return (new Path(array($overviewEiCommand->getWrapper()->getEiCommandPath())))->toUrl()
					->ext($overviewEiCommand->getOverviewUrlExt());
		}
		
		return null;
	}
	
// 	public function isAccessible(N2nContext $n2nContext) {
// 		$loginContext = $n2nContext->lookup(LoginContext::class);
// 		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
// 		$loginContext->getSecurityManager()->getEiPermissionManager()->isEiCommandAccessible(
// 				$this->eiMask->get)
// 	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\launch\LaunchPad::lookupController($n2nContext, $delegateControllerContext)
	 */
	public function lookupController(N2nContext $n2nContext, ControllerContext $delegateControllerContext): Controller {
		$manageState = $n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		$rocket = $n2nContext->lookup(Rocket::class);
		CastUtils::assertTrue($rocket instanceof Rocket);
		
		$em = $this->eiMask->getEiType()->lookupEntityManager($n2nContext->lookup(PdoPool::class));
		$manageState->setEntityManager($em);
		$manageState->setDraftManager($rocket->getOrCreateDraftManager($em));
		$manageState->setEiPermissionManager($loginContext->getSecurityManager()->getEiPermissionManager());
		
		$eiLifecycleMonitor = new EiLifecycleMonitor($rocket->getSpec());
		$eiLifecycleMonitor->initialize($manageState->getEntityManager(), $manageState->getDraftManager(), $n2nContext);
		$manageState->setEiLifecycleMonitor($eiLifecycleMonitor);
		
		return new EiController($this->eiMask);
	}

	public function approveTransaction(N2nContext $n2nContext): TransactionApproveAttempt {
		$manageState = $n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		
		return $manageState->getEiLifecycleMonitor()->approve($n2nContext);
	}
}
