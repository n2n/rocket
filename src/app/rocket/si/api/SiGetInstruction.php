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
use n2n\util\type\TypeName;

class SiGetInstruction {
	private $bulky;
	private $readOnly;
	private $declarationIncluded = true;
	private $entityIds = [];
	private $partialContentInstruction;
	private $numNews = 0;
	
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
	function setBulky($bulky) {
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
	function setReadOnly($readOnly) {
		$this->readOnly = $readOnly;
	}

	/**
	 * @return bool
	 */
	function isDeclarationIncluded() {
		return $this->declarationIncluded;
	}

	/**
	 * @param bool $declarationIncluded
	 */
	function setDeclarationIncluded(bool $declarationIncluded) {
		$this->declarationIncluded = $declarationIncluded;
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
	function setPartialContentInstruction(SiPartialContentInstruction $partialContentInstruction) {
		$this->partialContentInstruction = $partialContentInstruction;
	}

	/**
	 * @return number
	 */
	function getNumNews() {
		return $this->numNews;
	}

	/**
	 * @param number $numNews
	 */
	function setNumNews($numNews) {
		$this->numNews = $numNews;
	}

	/**
	 * @param multitype: $entityIds
	 */
	function setEntryIds($entityIds) {
		$this->entityIds = $entityIds;
	}


	
	/**
	 * @return string[]
	 */
	function getEntityIds() {
		return $this->entityIds;
	}
	
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		try {
			$instruction = new SiGetInstruction($ds->reqBool('bulky'), $ds->reqBool('readOnly'));
			$instruction->setDeclarationIncluded($ds->reqBool('declarationIncluded'));
			$instruction->setEntryIds($ds->reqArray('entryIds', TypeName::INT));
			
			$pcData = $ds->optArray('partialContentInstruction', null, null);
			if ($pcData == null) {
				$instruction->setPartialContentInstruction(null);
			} else {
				$instruction->setPartialContentInstruction(SiPartialContentInstruction::createFromData($pcData));
			}
			
			$instruction->setNumNews($ds->reqArray('numNews'));
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
			$instruction->setFrom($ds->reqInt('entries'));
			$instruction->setNum($ds->reqInt('numNews'));
			return $instruction;
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}

