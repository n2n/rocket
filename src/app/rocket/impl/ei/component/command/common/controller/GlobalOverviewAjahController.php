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
namespace rocket\impl\ei\component\command\common\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\impl\ScrController;
use rocket\user\model\LoginContext;
use rocket\ei\manage\ManageState;
use n2n\web\http\PageNotFoundException;
use rocket\ei\mask\EiMask;
use n2n\web\http\ForbiddenException;
use rocket\ei\EiCommandPath;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\EiType;
use rocket\core\model\Rocket;
use n2n\core\N2N;
use n2n\util\uri\Url;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\spec\UnknownTypeException;
use rocket\ei\UnknownEiTypeExtensionException;

class GlobalOverviewJhtmlController extends ControllerAdapter implements ScrController {
	private $manageState;
	private $rocket;
	private $loginContext;

	private function _init(ManageState $manageState, Rocket $rocket, LoginContext $loginContext) {
		$this->manageState = $manageState;
		$this->rocket = $rocket;
		$this->loginContext = $loginContext;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\controller\impl\ScrController::isValid()
	 */
	public function isValid(): bool {
		return ($this->loginContext->hasCurrentUser()
				&& $this->loginContext->getCurrentUser()->isAdmin()) || N2N::isDevelopmentModeOn();
	}

	public function doEis($eiTypeId, array $delegateCmds = array(), OverviewJhtmlController $overviewJhtmlController) {
		$eiType = null;
		try {
			$eiType = $this->rocket->getSpec()->getEiTypeById($eiTypeId);
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException();
		}

		$this->del($eiType->getEiTypeExtensionCollection()->getOrCreateDefault(), $overviewJhtmlController);
	}

	public function doEim($eiTypeId, $eiMaskId, array $delegateCmds = array(),
			OverviewJhtmlController $overviewJhtmlController) {

		$eiMask = null;
		try {
			$eiType = $this->rocket->getSpec()->getEiTypeById($eiTypeId);
			$eiMask = $eiType->getEiTypeExtensionCollection()->getById($eiMaskId);
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (UnknownEiTypeExtensionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}

		$this->del($eiMask, $overviewJhtmlController);
	}

	private function del(EiMask $eiMask, OverviewJhtmlController $overviewJhtmlController) {
		$n2nContext = $this->getN2nContext();
		$em = $eiMask->getEiEngine()->getEiMask()->getEiType()->lookupEntityManager($this->getN2nContext()->getPdoPool());
		$this->manageState->setEntityManager($em);
		$this->manageState->setDraftManager($n2nContext->lookup(Rocket::class)->getOrCreateDraftManager($em));
		$this->manageState->setEiPermissionManager($this->loginContext->getSecurityManager()->getEiPermissionManager());

		$controllerContext = $this->createDelegateContext($overviewJhtmlController);
		$eiFrame = $this->manageState->createEiFrame($eiMask, $controllerContext);

		try {
			$eiFrame->setEiExecution($this->manageState->getEiPermissionManager()
					->createUnboundEiExceution($eiMask, new EiCommandPath(array())));
		} catch (InaccessibleEiCommandPathException $e) {
			throw new ForbiddenException(null, 0, $e);
		}

		$this->delegateToControllerContext($controllerContext);
	}

	public static function buildToolsAjahUrl(ScrRegistry $scrRegistry, EiType $eiType, EiMask $eiMask = null): Url {
		$contextUrl = $scrRegistry->registerSessionScrController(GlobalOverviewJhtmlController::class);
		if ($eiMask !== null) {
			$contextUrl = $contextUrl->extR(array('eim', $eiType->getId(), $eiMask->getExtension()->getId()));
		} else {
			$contextUrl = $contextUrl->extR(array('eis', $eiType->getId()));
		}

		return OverviewJhtmlController::buildToolsAjahUrl($contextUrl);
	}
}
