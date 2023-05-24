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
namespace rocket\op\ei;

use rocket\op\launch\LaunchPad;
use rocket\op\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use n2n\web\http\controller\ControllerContext;
use n2n\web\http\controller\Controller;
use rocket\op\ei\manage\ManageState;
use n2n\util\type\CastUtils;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use n2n\core\container\PdoPool;
use rocket\op\launch\TransactionApproveAttempt;
use rocket\op\ei\manage\veto\EiLifecycleMonitor;
use rocket\op\ei\manage\frame\EiFrameController;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\EiLaunch;
use n2n\persistence\ext\EmPool;

class EiLaunchPad implements LaunchPad {
	private EiMask $eiMask;

	public function __construct(private readonly string $id, private ?\Closure $eiMaskCallback,
			private readonly ?string $label = null,
			private readonly bool $transactionalEmEnabled = true, private readonly ?string $persistenceUnitName = null,
			private int $orderIndex = 99999) {
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\launch\LaunchPad::getId()
	 */
	public function getId(): string {
		return $this->id;
	}

	public function getLabel(): string {
		return $this->label ?? $this->getEiMask()->getPluralLabelLstr();
	}

	function isTransactionalEmEnabled(): bool {
		return $this->transactionalEmEnabled;
	}

	function getPersistenceUnitName(): ?string {
		return $this->persistenceUnitName;
	}

	function getEiMask() {
		if ($this->eiMaskCallback === null) {
			IllegalStateException::assertTrue(isset($this->eiMask));
			return $this->eiMask;
		}

		$callback = $this->eiMaskCallback;
		$this->eiMaskCallback = null;
		$eiMask = $callback();
		ArgUtils::valTypeReturn($eiMask, EiMask::class, null, $callback);
		return $this->eiMask = $eiMask;
	}
	
	public function isAccessible(N2nContext $n2nContext): bool {
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		$overviewEiCommand = $this->getEiMask()->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd();
		
		return $loginContext->getSecurityManager()->createEiPermissionManager($n2nContext->lookup(ManageState::class))
				->isEiCommandAccessible($this->getEiMask(), $overviewEiCommand);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\launch\LaunchPad::determinePathExt($n2nContext)
	 */
	public function determinePathExt(N2nContext $n2nContext) {
		$result = $this->getEiMask()->getEiCmdCollection()->determineGenericOverview(true);
		
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		if ($loginContext->getSecurityManager()->createEiPermissionManager($n2nContext->lookup(ManageState::class))
				->isEiCommandAccessible($this->getEiMask(), $result->getEiCmd())) {
			return EiFrameController::createCmdUrlExt($result->getEiCmdPath());
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
	 * @see \rocket\op\launch\LaunchPad::lookupController($n2nContext, $delegateControllerContext)
	 */
	public function lookupController(N2nContext $n2nContext, ControllerContext $delegateControllerContext): Controller {
		$manageState = $n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		$rocket = $n2nContext->lookup(Rocket::class);
		CastUtils::assertTrue($rocket instanceof Rocket);

		$emf = $n2nContext->lookup(EmPool::class)->getEntityManagerFactory($this->persistenceUnitName);
		if ($this->transactionalEmEnabled) {
			$em = $emf->getTransactional();
		} else {
			$em = $emf->getExtended();
		}

		$manageState->setEntityManager($em);
//		$manageState->setDraftManager($rocket->getOrCreateDraftManager($em));
		$manageState->setEiPermissionManager($loginContext->getSecurityManager()->createEiPermissionManager($manageState));
		
		$eiLifecycleMonitor = new EiLifecycleMonitor($rocket->getSpec());
		$eiLifecycleMonitor->initialize($manageState->getEntityManager(), /*$manageState->getDraftManager(),*/ $n2nContext);
		$manageState->setEiLifecycleMonitor($eiLifecycleMonitor);

		$eiLaunch = new EiLaunch($n2nContext, $manageState->getEiPermissionManager(), $em);
		$eiFrame = $eiLaunch->createRootEiFrame($this->getEiMask()->getEiEngine());

		return new EiFrameController($eiFrame);
	}

	public function approveTransaction(N2nContext $n2nContext): TransactionApproveAttempt {
		$manageState = $n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		
		return $manageState->getEiLifecycleMonitor()->approve($n2nContext);
	}

	function getOrderIndex(): int {
		return $this->orderIndex;
	}

	function setOrderIndex(int $orderIndex): static {
		$this->orderIndex = $orderIndex;
		return $this;
	}
}
