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
namespace rocket\si\api;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use n2n\util\ex\IllegalStateException;

class SiGetInstruction {
	private $bulky;
	private $readOnly;
	private $declarationRequested = false;
	private $controlsIncluded = false;
	private $entryId = null;
	private $partialContentInstruction = null;
	private $newEntryRequested = false;
	
	/**
	 * @param bool $bulky
	 * @param bool $readOnly
	 */
	function __construct(bool $bulky, bool $readOnly) {
		$this->bulky = $bulky;
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @return bool
	 */
	function isBulky() {
		return $this->bulky;
	}

	/**
	 * @param bool $bulky
	 */
	function setBulky(bool $bulky) {
		$this->bulky = $bulky;
	}

	/**
	 * @return bool
	 */
	function isReadOnly() {
		return $this->readOnly;
	}

	/**
	 * @param bool $readOnly
	 */
	function setReadOnly(bool $readOnly) {
		$this->readOnly = $readOnly;
	}

	/**
	 * @return bool
	 */
	function isDeclarationRequested() {
		return $this->declarationRequested;
	}

	/**
	 * @param bool $declarationRequested
	 */
	function setDeclarationRequested(bool $declarationRequested) {
		$this->declarationRequested = $declarationRequested;
	}
	
	/**
	 * @return boolean
	 */
	function areControlsIncluded() {
		return $this->controlsIncluded;
	}
	
	/**
	 * @param boolean $controlsIncluded
	 */
	function setControlsIncluded(bool $controlsIncluded) {
		$this->controlsIncluded = $controlsIncluded;
	}

	/**
	 * @return mixed
	 */
	function getPartialContentInstruction() {
		return $this->partialContentInstruction;
	}

	/**
	 * @param mixed $partialContentInstruction
	 */
	function setPartialContentInstruction(?SiPartialContentInstruction $partialContentInstruction) {
		IllegalStateException::assertTrue(($this->entryId === null && !$this->newEntryRequested)
				|| $partialContentInstruction === null);
		$this->partialContentInstruction = $partialContentInstruction;
	}

	/**
	 * @return bool
	 */
	function isNewEntryRequested() {
		return $this->newEntryRequested;
	}

	/**
	 * @param int $newEntriesNum
	 */
	function setNewEntryRequested(bool $newEntryRequested) {
		IllegalStateException::assertTrue(($this->partialContentInstruction === null && $this->entryId === null)
				|| !$newEntryRequested);
		$this->newEntryRequested = $newEntryRequested;
	}

	/**
	 * @param string|null $entryId
	 */
	function setEntryId(?string $entryId) {
		IllegalStateException::assertTrue(($this->partialContentInstruction === null && !$this->newEntryRequested)
				|| $entryId === null);
		$this->entryId = $entryId;
	}
	
	/**
	 * @return string|null
	 */
	function getEntryId() {
		return $this->entryId;
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\api\SiGetInstruction
	 */
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		try {
			$instruction = new SiGetInstruction($ds->reqBool('bulky'), $ds->reqBool('readOnly'));
			$instruction->setDeclarationRequested($ds->reqBool('declarationRequested'));
			$instruction->setEntryId($ds->optInt('entryId'));
			
			$pcData = $ds->optArray('partialContentInstruction', null, null, true);
			if ($pcData == null) {
				$instruction->setPartialContentInstruction(null);
			} else {
				$instruction->setPartialContentInstruction(SiPartialContentInstruction::createFromData($pcData));
			}
			
			$instruction->setNewEntryRequested($ds->reqBool('newEntryRequested'));
			return $instruction;
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}


class SiPartialContentInstruction {
	private $from = 0;
	private $num = 0;
	
	/**
	 * @return int
	 */
	function getFrom() {
		return $this->from;
	}

	/**
	 * @param int $from
	 */
	function setFrom(int $from) {
		$this->from = $from;
	}

	/**
	 * @return int
	 */
	function getNum() {
		return $this->num;
	}

	/**
	 * @param int $num
	 */
	function setNum(int $num) {
		$this->num = $num;
	}

	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		try {
			$instruction = new SiPartialContentInstruction();
			$instruction->setFrom($ds->reqInt('offset'));
			$instruction->setNum($ds->reqInt('num'));
			return $instruction;
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}
