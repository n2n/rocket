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
namespace rocket\ei\manage\frame;

use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use rocket\ei\manage\ManageState;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\util\Eiu;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\SiApiController;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use n2n\web\http\BadRequestException;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\EiCommandPath;
use n2n\util\ex\NotYetImplementedException;

class EiFrameController extends ControllerAdapter {
	const API_PATH_PART = 'api';
	const CMD_PATH_PART = 'cmd';
	
	private $eiMask;	
	private $manageState;
	
	function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	function prepare(ManageState $manageState) {
		$this->manageState = $manageState;
	}
	
	/**
	 * @param string $str
	 * @return \rocket\ei\EiCommandPath
	 */
	private function parseEiCommandPath($str) {
		try {
			return EiCommandPath::create($str);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @throws PageNotFoundException
	 * @return \rocket\ei\component\command\EiCommand
	 */
	private function loookupEiCommand($eiCommandPath) {
		try {
			return $this->eiMask->getEiCommandCollection()->getByPath($eiCommandPath);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return EiFrame
	 */
	private function pushEiFrame($eiCommandPath) {
		$eiFrame = null;
		try {
			$eiFrame = $this->eiMask->getEiEngine()->createEiFrame($this->getControllerContext(), 
					$this->manageState, $this->manageState->peakEiFrame(false), $eiCommandPath);
		} catch (InaccessibleEiCommandPathException $e) {
			throw new ForbiddenException(null, 0, $e);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$this->manageState->pushEiFrame($eiFrame);
		
		return $eiFrame;
	}
	
	public function doApi($eiCommandPathStr, SiApiController $siApiController, array $delegateParams = null) {
		$eiCommandPath = $this->parseEiCommandPath($eiCommandPathStr);
		$this->pushEiFrame($eiCommandPath);
		
		$this->delegate($siApiController);
	}
	
	public function doCmd($eiCommandPathStr, array $delegateCmds = null) {		
		$eiCommandPath = $this->parseEiCommandPath($eiCommandPathStr);
		$eiCommand = $this->loookupEiCommand($eiCommandPath);
		
		$eiFrame = $this->pushEiFrame($eiCommandPath);
		
		try {
			$this->delegate($eiCommand->lookupController(new Eiu($eiFrame)));
		} catch (UnsupportedOperationException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	public function doField($eiPropPathStr, array $delegateCmds = null) {
		throw new NotYetImplementedException();
	}
}
