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
namespace rocket\spec\ei;

use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\component\UnknownEiComponentException;
use rocket\spec\ei\security\InaccessibleControlException;
use rocket\spec\ei\manage\util\model\Eiu;

class EiSpecController extends ControllerAdapter {
		
	public function index(ManageState $manageState, $eiCommandId, array $delegateCmds = null) {		
		$eiFrame = $manageState->peakEiFrame();
		
		$eiCommand = null;
		try {
			$eiCommand = $eiFrame->getContextEiMask()->getEiEngine()->getEiCommandCollection()->getById($eiCommandId);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		try {
			$eiFrame->setEiExecution($manageState->getEiPermissionManager()
					->createEiExecution($eiCommand, $this->getN2nContext()));
		} catch (InaccessibleControlException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		$this->delegate($eiCommand->lookupController(new Eiu($eiFrame)));
	}
}
