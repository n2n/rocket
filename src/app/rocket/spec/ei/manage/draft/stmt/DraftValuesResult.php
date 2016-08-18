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
namespace rocket\spec\ei\manage\draft\stmt;

use rocket\spec\ei\EiFieldPath;

class DraftValuesResult {
	private $id;
	private $entityObjId;
	private $lastMod;
	private $flag;
	private $userId;
	private $values;

	public function __construct(int $id, $entityObjId, \DateTime $lastMod, string $flag = null, int $userId, 
			array $values) {
		$this->id = $id;
		$this->entityObjId = $entityObjId;
		$this->lastMod = $lastMod;
		$this->flag = $flag;
		$this->userId = $userId;
		$this->values = $values;
	}
	
	/**
	 * @return int $id
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return mixed $entityObjId
	 */
	public function getEntityObjId() {
		return $this->entityObjId;
	}

	/**
	 * @return \DateTime $lastMod
	 */
	public function getLastMod(): \DateTime {
		return $this->lastMod;
	}

	/**
	 * @return string
	 */
	public function getFlag() {
		return $this->flag;
	}

	/**
	 * @return int $userId
	 */
	public function getUserId(): int {
		return $this->userId;
	}

	/**
	 * @return array $values
	 */
	public function getValues(): array {
		return $this->values;
	}
	
	/**
	 * @param EiFieldPath $eiFieldPath
	 * @return mixed
	 */
	public function getValue(EiFieldPath $eiFieldPath) {
		$eiFieldPathStr = (string) $eiFieldPath;
		if (isset($this->values[$eiFieldPathStr])) {
			return $this->values[$eiFieldPathStr];
		}
		return null;
	}
}
