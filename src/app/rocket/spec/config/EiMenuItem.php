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
namespace rocket\spec\config;

use rocket\core\model\MenuItem;
use rocket\spec\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use n2n\web\http\controller\ControllerContext;
use n2n\web\http\controller\Controller;
use rocket\spec\ei\manage\ManageState;
use n2n\reflection\CastUtils;
use rocket\core\model\Rocket;
use rocket\spec\ei\EiSpecController;
use rocket\user\model\LoginContext;
use n2n\core\container\PdoPool;
use rocket\spec\ei\EiSpec;
use n2n\util\uri\Path;

class EiMenuItem implements MenuItem {
	private $id;
	private $eiSpec;
	private $eiMask;
	private $label;
	
	public function __construct(string $id, EiSpec $eiSpec, EiMask $eiMask, string $label = null) {
		$this->id = $id;
		$this->eiSpec = $eiSpec;
		$this->eiMask = $eiMask;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\MenuItem::getId()
	 */
	public function getId(): string {
		return $this->id;
	}

	public function getLabel(): string {
		if ($this->label !== null) {
			return $this->label;
		}
	
		return $this->eiMask->getPluralLabelLstr();
	}
	
	public function isAccessible(N2nContext $n2nContext): bool {
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		$overviewEiCommand = $this->eiMask->getEiEngine()->getEiCommandCollection()
				->getGenericOverviewEiCommand(true);
		
		return $loginContext->getSecurityManager()->getEiPermissionManager()
				->isEiCommandAccessible($overviewEiCommand);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\MenuItem::determinePathExt($n2nContext)
	 */
	public function determinePathExt(N2nContext $n2nContext) {
		$overviewEiCommand = $this->eiMask->getEiEngine()->getEiCommandCollection()
				->getGenericOverviewEiCommand(true);
		
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		if ($loginContext->getSecurityManager()->getEiPermissionManager()->isEiCommandAccessible($overviewEiCommand)) {
			return (new Path(array($overviewEiCommand->getId())))->toUrl()->ext($overviewEiCommand->getOverviewUrlExt());
		}
	}
	
// 	public function isAccessible(N2nContext $n2nContext) {
// 		$loginContext = $n2nContext->lookup(LoginContext::class);
// 		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
// 		$loginContext->getSecurityManager()->getEiPermissionManager()->isEiCommandAccessible(
// 				$this->eiMask->get)
// 	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\MenuItem::lookupController($n2nContext, $delegateControllerContext)
	 */
	public function lookupController(N2nContext $n2nContext, ControllerContext $delegateControllerContext): Controller {
		$manageState = $n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		$loginContext = $n2nContext->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
				
		$manageState->createEiState($this->eiMask, $delegateControllerContext);
		$em = $this->eiSpec->lookupEntityManager($n2nContext->lookup(PdoPool::class));
		$manageState->setEntityManager($em);
		$manageState->setDraftManager($n2nContext->lookup(Rocket::class)->getOrCreateDraftManager($em));
		$manageState->setEiPermissionManager($loginContext->getSecurityManager()->getEiPermissionManager());
		
		return $n2nContext->lookup(EiSpecController::class);
	}
}
