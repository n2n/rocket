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
namespace rocket\ei\component\command;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\UnknownEiComponentException;
use n2n\util\type\ArgUtils;
use rocket\ei\mask\EiMask;
use rocket\ei\EiCmdPath;
use rocket\ei\util\Eiu;
use rocket\ei\manage\EiObject;
use rocket\si\control\SiNavPoint;
use rocket\ei\manage\gui\GuiDefinition;
use n2n\core\container\N2nContext;

class EiCmdCollection extends EiComponentCollection {
	
	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiCommand', EiCmdNature::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param string $id
	 * @return EiCmd
	 * @throws UnknownEiComponentException;
	 */
	public function getByPath(EiCmdPath $eiCmdPath) {
		return $this->getElementByIdPath($eiCmdPath);
	}
	
	/**
	 * @param string $id
	 * @return EiCmdNature
	 */
	public function getById(string $id) {
		return $this->getElementByIdPath(new EiCmdPath($id));
	}

	/**
	 * @param string|null $id
	 * @param EiCmdNature $eiCmd
	 * @return EiCmd
	 */
	public function add(?string $id, EiCmdNature $eiCmd): EiCmd {
		$eiCmdPath = new EiCmdPath($this->makeId($id, $eiCmd));
		$eiCmd = new EiCmd($eiCmdPath, $eiCmd, $this);
		
		$this->addEiComponent($eiCmdPath, $eiCmd);
		
		return $eiCmd;
	}
	
	/**
	 * @return boolean
	 */
	public function hasGenericOverview() {
		return null !== $this->determineGenericOverview(false);
	}
	
	/**
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\component\command\GenericResult|NULL
	 */
	public function determineGenericOverview(bool $required) {
		foreach ($this as $eiCmd) {
			$navPoint = $eiCmd->getNature()->buildOverviewNavPoint(new Eiu($this->eiMask, $eiCmd));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCmd, 'buildOverviewNavPoint', 
// 					'Returned Url must be relative.');
			
			return new GenericResult($eiCmd, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask . ' provides no generic overview EiCommand.');
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function hasGenericDetail(EiObject $eiObject) {
		return null !== $this->determineGenericDetail($eiObject, false);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return GenericResult
	 */
	public function determineGenericDetail(EiObject $eiObject, bool $required = false) {
		foreach ($this->eiMask->getEiCmdCollection() as $eiCmd) {
			$navPoint = $eiCmd->getNature()->buildDetailNavPoint(new Eiu($this->eiMask, $eiObject, $eiCmd));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCmd,
// 					'getDetailUrlExt', 'Returned Url must be relative.');
			
			return new GenericResult($eiCmd, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask->getEiEngineModel()
				. ' provides no generic detail EiCommand for ' . $eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function hasGenericEdit(EiObject $eiObject) {
		return null !== $this->determineGenericEdit($eiObject, false);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return GenericResult
	 */
	public function determineGenericEdit(EiObject $eiObject, bool $required = false) {
		foreach ($this->eiMask->getEiCommandCollection() as $eiCmd) {
			$navPoint = $eiCmd->getNature()->buildEditNavPoint(new Eiu($this->eiMask, $eiObject, $eiCmd));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($urlExt->isRelative(), $eiCmd,
// 					'getEditUrlExt', 'Returned Url must be relative.');
			
			return new GenericResult($eiCmd, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask->getEiEngineModel()
				. ' provides no generic edit EiCommand for ' . $eiObject);
	}
	
		/**
	 * @return boolean
	 */
	public function hasGenericAdd() {
		return null !== $this->determineGenericAdd(false);
	}
	
	/**
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\component\command\GenericResult|NULL
	 */
	public function determineGenericAdd(bool $required) {
		foreach ($this as $eiCmd) {
			$navPoint = $eiCmd->getNature()->buildAddNavPoint(new Eiu($this->eiMask, $eiCmd));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCmd,
// 					'buildAddNavPoint', 'Returned Url must be relative.');
			
			return new GenericResult($eiCmd, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask . ' provides no generic add EiCommand.');
	}

	function supplyGuiDefinition(GuiDefinition $guiDefinition) {
		ArgUtils::assertTrue($guiDefinition->getEiMask() === $this->eiMask);

		foreach ($this->eiMask->getEiCmdCollection() as $eiCmd) {
			$eiCmdPath = $eiCmd->getEiCmdPath();

			if (null !== ($guiCommand = $eiCmd->getNature()
							->buildGuiCommand(new Eiu($this->eiMask, $eiCmdPath)))) {
				$guiDefinition->putGuiCommand($eiCmdPath, $guiCommand);
			}
		}
	}
}

class GenericResult {
	private $eiCmd;
	private $eiCmdPath;
	private $navPoint;
	
	function __construct(EiCmd $eiCmd, SiNavPoint $navPoint) {
		$this->eiCmd = $eiCmd;
		$this->eiCmdPath = EiCmdPath::from($eiCmd);
		$this->navPoint = $navPoint;
	}
	
	function getEiCmd() {
		return $this->eiCmd;	
	}
	
	/**
	 * @return \rocket\ei\EiCmdPath
	 */
	function getEiCmdPath() {
		return $this->eiCmdPath;
	}
	
	/**
	 * @return SiNavPoint
	 */
	function getNavPoint() {
		return $this->navPoint;
	}
	
}
