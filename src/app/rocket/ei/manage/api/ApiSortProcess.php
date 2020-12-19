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
namespace rocket\ei\manage\api;

use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\gui\control\EntryGuiControl;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\frame\EiFrame;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\si\control\SiResult;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInput;
use rocket\si\input\SiError;
use rocket\si\input\SiEntryInput;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\util\ex\IllegalStateException;
use rocket\spec\TypePath;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\si\input\SiInputFactory;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\UnknownEiTypeException;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use n2n\web\http\ForbiddenException;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\gui\EiGui;
use n2n\persistence\orm\util\UnknownEntryException;
use n2n\web\http\StatusException;
use n2n\web\http\Response;
use rocket\ei\manage\EiObject;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\frame\SortAbility;

class ApiSortProcess {
	private $eiFrame;
	/**
	 * @var ProcessUtil
	 */
	private $util;
	/**
	 * @var EiFrameUtil
	 */
	private $eiFrameUtil;
	
	/**
	 * @var SortAbility
	 */
	private $sortAbility;
	/**
	 * @var EiObject[]
	 */
	private $eiObjects;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
		$this->util = new ProcessUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
		
		$this->sortAbility = $eiFrame->getAbility()->getSortAbility();
		if ($this->sortAbility === null) {
			throw new BadRequestException('No SortAbility!');
		}
	}

	/**
	 * @param string $pid
	 * @return EiObject
	 * @throws BadRequestException
	 */
	private function lookupEiObject($pid) {
		try {
			return $this->eiFrameUtil->lookupEiObject($pid);
		} catch (UnknownEntryException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	/**
	 * @param string[] $pids
	 * @throws BadRequestException
	 */
	function determineEiObjects(array $pids) {
		$this->eiObjects = [];
		foreach ($pids as $pid) {
			$this->eiObject[] = $this->lookupEiObject($pid);
		}
	}
	
	function insertAfter(string $pid) {
		$eiObject = $this->lookupEiObject($pid);
		
		return $this->sortAbility->insertAfter($this->eiObjects, $eiObject);
	}
	
	function insertBefore(string $pid) {
		$eiObject = $this->lookupEiObject($pid);
		
		return $this->sortAbility->insertBefore($this->eiObjects, $eiObject);
	}
	
	function insertAsChildOf(string $pid) {
		$eiObject = $this->lookupEiObject($pid);
		
		if (null === $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getNestedSetStrategy()) {
			throw new BadRequestException('Tree sort not available.');
		}
		
		return $this->sortAbility->insertAsChild($this->eiObjects, $eiObject);
	}
	
}