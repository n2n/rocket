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
namespace rocket\op\ei\manage\frame;

use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\component\UnknownEiComponentException;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\security\InaccessibleEiCmdPathException;
use n2n\web\http\BadRequestException;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\manage\EiObject;
use n2n\util\uri\Url;
use n2n\util\uri\Path;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\EiType;
use rocket\op\ei\component\command\EiCmd;

class EiFrameController extends ControllerAdapter {
	const API_PATH_PART = 'api';
	const CMD_PATH_PART = 'cmd';
	const FORK_PATH = 'fork';
	const FORK_ENTRY_PATH = 'forkentry';
	const FORK_NEW_ENTRY_PATH = 'forknewentry';
	
	private $eiFrame;	
	private $manageState;
	
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	function prepare(ManageState $manageState) {
		$this->manageState = $manageState;
		$this->eiFrame->setBaseUrl($this->getUrlToController());
	}
	
	/**
	 * @param string $str
	 * @return \rocket\op\ei\EiCmdPath
	 */
	private function parseEiCmdPath($str) {
		try {
			return EiCmdPath::create($str);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $str
	 * @return \rocket\op\ei\EiPropPath
	 */
	private function parseEiPropPath($str) {
		try {
			return EiPropPath::create($str);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $mode
	 * @param EiObject|null $eiObject
	 * @throws BadRequestException
	 */
	private function createEiForkLink($mode, $eiObject) {
		try {
			return new EiForkLink($this->eiFrame, $mode, $eiObject);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param EiCmdPath $eiCmdPath
	 * @return EiCmd
	 *@throws PageNotFoundException
	 */
	private function lookupEiCmd($eiCmdPath): EiCmd {
		try {
			return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiCmdCollection()
					->getByPath($eiCmdPath);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $pid
	 * @return \rocket\op\ei\manage\LiveEiObject
	 */
	private function lookupEiObject($pid) {
		$util = new EiObjectSelector($this->eiFrame);
		
		try {
			return new LiveEiObject($util->lookupEiEntityObj($util->pidToId($pid)));
		} catch (UnknownEiObjectException $e) {
			throw new PageNotFoundException(null, 0, $e);	
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $eiTypeId
	 * @throws BadRequestException
	 * @return \rocket\op\ei\manage\EiObject
	 */
	private function createEiObject($eiTypeId) {
		$util = new EiObjectSelector($this->eiFrame);
		
		try {
			return $util->createNewEiObject($eiTypeId);
		} catch (\rocket\op\ei\UnknownEiTypeException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param EiCmdPath $eiCmdPath
	 * @param EiCmdNature $eiCmd
	 * @return EiFrame
	 */
	private function pushEiFrame($eiCmd) {
		$eiFrame = null;
		try {
			$this->eiFrame->setBaseUrl($this->getUrlToController(null, null, $this->getControllerContext()));
			$this->eiFrame->exec($eiCmd);
		} catch (InaccessibleEiCmdPathException $e) {
			throw new ForbiddenException(null, 0, $e);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$this->manageState->pushEiFrame($this->eiFrame);
		
		return $eiFrame;
	}
	
	/**
	 * @param string $eiTypeId
	 * @throws PageNotFoundException
	 * @return \rocket\op\ei\EiType
	 */
	private function lookupEiType($eiTypeId) {
		try {
			return (new EiObjectSelector($this->eiFrame))->getEiTypeById($eiTypeId);
		} catch (\rocket\op\ei\UnknownEiTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	/**
	 * @param EiType $eiType
	 * @param EiPropPath $eiPropPath
	 * @throws ForbiddenException
	 * @throws PageNotFoundException
	 * @return \rocket\op\ei\manage\frame\EiFrame
	 */
	private function createForked($eiType, $eiPropPath, $eiForkLink) {
		try {
			$eiEngine = $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMask($eiType, false)->getEiEngine();
			return $eiEngine->forkEiFrame($eiPropPath, $eiForkLink);
		} catch (InaccessibleEiCmdPathException $e) {
			throw new ForbiddenException(null, 0, $e);
		} catch (UnknownEiComponentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} 
	}
	
	public function doApi($eiCmdPathStr, ApiController $apiController, ?array $delegateParams = null) {
		$eiCmdPath = $this->parseEiCmdPath($eiCmdPathStr);
		$eiCmd = $this->lookupEiCmd($eiCmdPath);

		$this->pushEiFrame($eiCmd);
		
		$this->delegate($apiController);
	}

	/**
	 * @throws PageNotFoundException
	 * @throws BadRequestException
	 * @throws ForbiddenException
	 */
	public function doCmd($eiCmdPathStr, ?array $delegateCmds = null): void {
		$eiCmdPath = $this->parseEiCmdPath($eiCmdPathStr);
		$eiCmd = $this->lookupEiCmd($eiCmdPath);
		$this->pushEiFrame($eiCmd);

		$controller = $eiCmd->getNature()->lookupController(new Eiu($this->eiFrame));
		if ($controller !== null) {
			$this->delegate($controller);
			return;
		}
		
		throw new PageNotFoundException(null, 0);		
	}
	
// 	public function doField($eiPropPathStr, array $delegateCmds) {
// 		$eiPropPath = $this->parseEiPropPath($eiPropPathStr);
// 	}
	
	public function doFork($eiCmdPathStr, $eiPropPathStr, $mode, array $delegateCmds) {
		$eiCmdPath = $this->parseEiCmdPath($eiCmdPathStr);
		$eiCmd = $this->lookupEiCmd($eiCmdPath);
		
		$this->pushEiFrame($eiCmd);
		
		$eiPropPath = $this->parseEiPropPath($eiPropPathStr);
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$eiForkLink = $this->createEiForkLink($mode, null);
		
		$this->delegate(new EiFrameController($this->createForked($eiType, $eiPropPath, $eiForkLink)));
	}
	
	public function doForkEntry($eiCmdPathStr, $pid, $eiPropPathStr, $mode, array $deleteCmds) {
		$eiCmdPath = $this->parseEiCmdPath($eiCmdPathStr);
		$eiCmd = $this->lookupEiCmd($eiCmdPath);
		$this->pushEiFrame($eiCmd);
		
		$eiPropPath = $this->parseEiPropPath($eiPropPathStr);
		$eiObject = $this->lookupEiObject($pid);
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		$eiForkLink = $this->createEiForkLink($mode, $eiObject);
		$this->delegate(new EiFrameController($this->createForked($eiType, $eiPropPath, $eiForkLink)));
	}
	
	public function doForkNewEntry($eiCmdPathStr, $eiTypeId, $eiPropPathStr, $mode, array $deleteCmds) {
		$eiCmdPath = $this->parseEiCmdPath($eiCmdPathStr);
		$eiCmd = $this->lookupEiCmd($eiCmdPath);
		
		$this->pushEiFrame($eiCmd);
		
		$eiPropPath = $this->parseEiPropPath($eiPropPathStr);
		$eiType = $this->lookupEiType($eiTypeId);
		$eiForkLink = $this->createEiForkLink($mode, $this->createEiObject($eiTypeId));
		
		$this->delegate(new EiFrameController($this->createForked($eiType, $eiPropPath, $eiForkLink)));
	}
	
	/**
	 * @param Url $urlExt
	 * @return Url
	 */
	static function createCmdUrlExt(EiCmdPath $eiCmdPath) {
		return (new Path([self::CMD_PATH_PART]))->toUrl()->ext((string) $eiCmdPath);
	}
}
