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

namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiState;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\command\impl\common\controller\EiCtrlUtils;
use rocket\spec\ei\manage\util\model\EiEntryObjUtils;

class EiEntryUtils {
	private $eiSelection;
	private $eiMapping;
	private $eiUtils;
	private $eiStateUtils;
	
	public function __construct($eiEntryObj, $eiState = null) {
		$this->eiSelection = EiEntryObjUtils::determineEiSelection($eiEntryObj, $this->eiMapping);
		
		if ($eiState instanceof EiState) {
			$this->eiUtils = $this->eiStateUtils = new EiStateUtils($eiState);
		} else if ($eiState instanceof EiCtrlUtils) {
			$this->eiUtils = $this->eiStateUtils = $eiState->getEiStateUtils();
		} else if ($eiState instanceof EiStateUtils) {
			$this->eiUtils = $this->eiStateUtils = $eiState;
		} else if ($eiState instanceof EiUtils) {
			$this->eiUtils = $eiState;
		} else if($eiState !== null) {
			ArgUtils::valType($eiState, array(EiState::class, EiCtrlUtils::class, EiStateUtils::class, EiUtils::class), 
					true, 'eiState');
		}	
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\spec\ei\manage\util\model\EiUtils
	 */
	public function getEiUtils() {
		if ($this->eiUtils !== null) {
			return $this->eiUtils;
		}
	
		throw new IllegalStateException('No EiUtils provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\spec\ei\manage\util\model\EiStateUtils
	 */
	public function getEiStateUtils() {
		if ($this->eiStateUtils !== null) {
			return $this->eiStateUtils;
		}
		
		throw new IllegalStateException('No EiState provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiState
	 */
	public function getEiState() {
		return $this->getEiStateUtils()->getEiState();
	}
	
	public function getEiMapping(bool $createIfNotAvaialble = true) {
		if ($this->eiMapping !== null) {
			return $this->eiMapping;
		}
		
		if ($createIfNotAvaialble) {
			return $this->eiMapping = $this->eiUtils->createEiMapping($this->eiSelection);
		}
		
		throw new IllegalStateException('No EiMapping available.');
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiSelection
	 */
	public function getEiSelection() {
		return $this->eiSelection;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\LiveEntry
	 */
	public function getLiveEntry() {
		return $this->eiSelection->getLiveEntry();
	}
	
	/**
	 * @return boolean
	 */
	public function isLivePersistent() {
		return $this->eiSelection->getLiveEntry()->isPersistent();
	}
	
	public function hasLiveId() {
		return $this->eiSelection->getLiveEntry()->hasId();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getLiveId(bool $required = true) {
		$liveEntry = $this->getLiveEntry();
		
		if (!$required && !$liveEntry->isPersistent()) {
			return null;
		}
		
		return $liveEntry->getId();
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpec() {
		return $this->getLiveEntry()->getEiSpec();
	}
	
	/**
	 * @param bool $required
	 * @return string
	 */
	public function getIdRep(bool $required = true) {
		return $this->getEiSpec()->idToIdRep($this->getLiveId($required));
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->eiSelection->isDraft();
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\spec\ei\manage\draft\Draft
	 */
	public function getDraft(bool $required = true) {
		if (!$required && !$this->isDraft()) {
			return null;
		}
		
		return $this->eiSelection->getDraft();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraftNew() {
		return $this->getDraft()->isNew();
	}
	
	/**
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(bool $determineEiMask = true, N2nLocale $n2nLocale = null) {
		return $this->eiStateUtils->createIdentityString($this->eiSelection, $determineEiMask, $n2nLocale);
	}
	
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \rocket\spec\ei\manage\draft\Draft[]
	 */
	public function lookupDrafts(int $limit = null, int $num = null) {
		return $this->eiStateUtils->lookupDraftsByEntityObjId($this->getLiveId(), $limit, $num);
	}
	
	public function isPreviewAvailable() {
		return !empty($this->eiStateUtils->getPreviewTypeOptions($this->eiSelection));
	}
	
	public function getPreviewType() {
		return $this->getEiStateUtils()->getPreviewType($this->eiSelection);
	}
	
	public function getPreviewTypeOptions() {
		return $this->eiStateUtils->getPreviewTypeOptions($this->eiSelection);
	}
}
