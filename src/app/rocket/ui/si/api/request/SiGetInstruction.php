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
namespace rocket\ui\si\api\request;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStyle;
use rocket\ui\si\err\CorruptedSiDataException;

class SiGetInstruction implements \JsonSerializable {
	private $declarationRequested = false;
	private $generalControlsIncluded = false;
	private $entryControlsIncluded = false;
	private ?string $entryId = null;
	private SiPartialContentInstruction|null $partialContentInstruction = null;
	private bool $newEntryRequested = false;
	private ?array $allowedFieldNames = null;

	function __construct(private string $maskId) {
	}

	function getMaskId(): string {
		return $this->maskId;
	}

//	function getAllowedMaskIds(): ?array {
//		return $this->allowedMaskIds;
//	}
//
//	function setAllowedMaskIds(?array $allowedMaskIds) {
//		ArgUtils::valArray($allowedMaskIds, 'string', true);
//		$this->allowedMaskIds = $allowedMaskIds;
//	}
	
	/**
	 * @return bool
	 */
	function isDeclarationRequested(): bool {
		return $this->declarationRequested;
	}

	/**
	 * @param bool $declarationRequested
	 */
	function setDeclarationRequested(bool $declarationRequested): void {
		$this->declarationRequested = $declarationRequested;
	}
	
	/**
	 * @return boolean
	 */
	function areGeneralControlsIncluded() {
		return $this->generalControlsIncluded;
	}
	
	/**
	 * @param boolean $controlsIncluded
	 */
	function setGeneralControlsIncluded(bool $controlsIncluded) {
		$this->generalControlsIncluded = $controlsIncluded;
	}
	
	/**
	 * @return boolean
	 */
	function areEntryControlsIncluded() {
		return $this->entryControlsIncluded;
	}
	
	/**
	 * @param boolean $controlsIncluded
	 */
	function setEntryControlsIncluded(bool $controlsIncluded) {
		$this->entryControlsIncluded = $controlsIncluded;
	}

	/**
	 * @return SiPartialContentInstruction
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
	
	function setAllowedFieldNames(?array $allowedFieldNames) {
		ArgUtils::valArray($allowedFieldNames, 'string', true);
		$this->allowedFieldNames = $allowedFieldNames;
	}
	
	/**
	 * @return string[]|null
	 */
	function getAllowedFieldNames() {
		return $this->allowedFieldNames;
	}

	function jsonSerialize(): array {
		return [
			'maskId' => $this->maskId,
			'entryId' => $this->entryId,
			'partialContentInstruction' => $this->partialContentInstruction,
			'newEntryRequested' => $this->newEntryRequested,
			'generalControlsIncluded' => $this->generalControlsIncluded,
			'entryControlsIncluded' => $this->entryControlsIncluded,
			'declarationRequested' => $this->declarationRequested
		];
	}

	/**
	 * @param array $data
	 * @return SiGetInstruction
	 * @throws CorruptedSiDataException
	 * @throws \InvalidArgumentException
	 */
	static function parse(array $data): SiGetInstruction {
		$ds = new DataSet($data);
		
		try {
			$instruction = new SiGetInstruction($ds->reqString('maskId'));
			$instruction->setDeclarationRequested($ds->reqBool('declarationRequested'));
			$instruction->setGeneralControlsIncluded($ds->reqBool('generalControlsIncluded'));
			$instruction->setEntryControlsIncluded($ds->reqBool('entryControlsIncluded'));
			$instruction->setEntryId($ds->optString('entryId'));
//			$instruction->setAllowedMaskIds($ds->optArray('typeIds', 'string', null, true));
			
			$pcData = $ds->optArray('partialContentInstruction', null, null, true);
			if ($pcData == null) {
				$instruction->setPartialContentInstruction(null);
			} else {
				$instruction->setPartialContentInstruction(SiPartialContentInstruction::createFromData($pcData));
			}
			
			$instruction->setNewEntryRequested($ds->reqBool('newEntryRequested'));
//			$instruction->setAllowedFieldNames($ds->reqScalarArray('propIds', true));
			return $instruction;
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('Could not parse SiGetInstruction.', 0, $e);
		}
	}
}


class SiPartialContentInstruction {

	function __construct(private int $from = 0, private int $num = 0, private ?string $quickSearchStr = null) {

	}

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
	
	/**
	 * @return string|null
	 */
	function getQuickSearchStr() {
		return $this->quickSearchStr;	
	}
	
	/**
	 * @param string|null $quickSearchStr
	 */
	function setQuickSearchStr(?string $quickSearchStr) {
		$this->quickSearchStr = $quickSearchStr;
	}

	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		try {
			$instruction = new SiPartialContentInstruction();
			$instruction->setFrom($ds->reqInt('offset'));
			$instruction->setNum($ds->reqInt('num'));
			$instruction->setQuickSearchStr($ds->optString('quickSearchStr'));
			return $instruction;
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}

