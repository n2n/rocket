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
namespace rocket\spec\ei\mask;

use rocket\spec\config\mask\CommonEiMask;
use n2n\io\IoUtils;
use rocket\spec\config\InvalidEiMaskConfigurationException;
use rocket\spec\config\mask\model\DisplayScheme;
use rocket\spec\ei\EiType;

class EiMaskCollection implements \IteratorAggregate, \Countable {
	private $eiType;
	private $eiMasks = array();
	private $commonEiMasks = array();
	private $defaultId;
	private $createdDefault = null;
	
	public function __construct(EiType $eiType) {
		$this->eiType = $eiType;
	}
	
	public function add(EiMask $eiMask) {
		$id = $eiMask->getId();
		if (0 == mb_strlen($id)) {
			$eiMask->setId($this->makeUniqueId(''));
		} else if (IoUtils::hasSpecialChars($id)) {
			throw new InvalidEiMaskConfigurationException('Id of passed EiMask contains invalid characters: ' . $id);
		}
	
		$this->eiMasks[$eiMask->getId()] = $eiMask;
	}
	
	public function addCommon(CommonEiMask $commonEiMask) {
		$this->add($commonEiMask);
		$this->commonEiMasks[$commonEiMask->getId()] = $commonEiMask;
	}
	
	/**
	 * @return CommonEiMask[]
	 */
	public function getCommons() {
		return $this->commonEiMasks;
	}
	
	/**
	 * @param string $id
	 * @return EiMask
	 * @throws UnknownEiMaskException
	 */
	public function getById($id): EiMask {
		if (isset($this->eiMasks[$id])) {
			return $this->eiMasks[$id];
		}
	
		throw new UnknownEiMaskException('No EiMask with id \'' . (string) $id
				. '\' found in  \'' . $this->eiType->getId() . '\'.');
	}
	
	public function setDefaultId($defaultId) {
		$this->defaultId = $defaultId;
	}
	
	public function getDefault() {
		if (isset($this->eiMasks[$this->defaultId])) {
			return $this->eiMasks[$this->defaultId];
		}
	
		return null;
	}
	
	public function getOrCreateDefault(): EiMask {
		if (isset($this->eiMasks[$this->defaultId])) {
			return $this->eiMasks[$this->defaultId];
		}
	
		if ($this->createdDefault === null) {
			$this->createdDefault = new CommonEiMask($this->eiType, $this->eiType->getModuleNamespace(), 
					new DisplayScheme());
		}
	
		return $this->createdDefault;
	
	}
	
	public function isEmpty(): bool {
		return empty($this->eiMasks);
	}
	
	/**
	 * @param string $idBase
	 * @return string
	 */
	public function makeUniqueId(string $idBase) {
		$idBase = IoUtils::stripSpecialChars($idBase, true);
		if (mb_strlen($idBase) && !$this->containsId($idBase)) {
			return $idBase;
		}
	
		for ($ext = 1; true; $ext++) {
			$id = $idBase . $ext;
			if (!$this->containsId($id)) {
				return $id;
			}
		}
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsId(string $id): bool {
		return isset($this->eiMasks[$id]);
	}
	
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->toArray());
	}
	
	/**
	 * @return EiMask[]
	 */
	public function toArray(): array {
		return $this->eiMasks;
	}
	/* (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return count($this->eiMasks);
	}
}
