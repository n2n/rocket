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

use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use rocket\ei\manage\ManageState;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\ei\util\Eiu;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\frame\EiFrame;

class EiController extends ControllerAdapter {
	private $eiMask;	
	private $eiFrame;
	
	public function __construct(EiMask $eiMask, EiFrame $eiFrame = null) {
		$this->eiMask = $eiMask;
		$this->eiFrame = $eiFrame;
	}
	
	public function index(ManageState $manageState, $eiCommandId, array $delegateCmds = null) {		
		$eiCommand = null;
		try {
			$eiCommand = $this->eiMask->getEiCommandCollection()->getByPath(EiCommandPath::create($eiCommandId));
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$eiFrame = $this->eiFrame;
		if ($eiFrame === null) {
			try {
				$eiFrame = $manageState->createEiFrame($this->eiMask->getEiEngine(), $this->getControllerContext(), EiCommandPath::from($eiCommand));
			} catch (InaccessibleEiCommandPathException $e) {
				throw new ForbiddenException(null, 0, $e);
			}
		} else {
			$eiFrame->getEiExecution()->extEiCommandPath(EiCommandPath::from($eiCommand));
		}
		
		$this->delegate($eiCommand->lookupController(new Eiu($eiFrame)));
	}
}
