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
namespace rocket\ei\manage\gui;

use n2n\util\ex\IllegalStateException;
use rocket\si\input\SiEntryInput;
use rocket\ei\EiType;
use n2n\util\type\ArgUtils;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\SiEntryIdentifier;
use rocket\ei\manage\entry\EiEntry;

class EiEntryGui {
	
	/**
	 * @var EiType
	 */
	private $contextEiType;
	/**
	 * @var string|null
	 */
	private $selectedEiTypeId = null;
	/**
	 * @var EiEntryGuiTypeDef[]
	 */
	private $typeDefs = [];
	/**
	 * @var int|null
	 */
	private $treeLevel;
	
	/**
	 * @param int|null $treeLevel
	 */
	public function __construct(EiType $contextEiType, int $treeLevel = null) {
		$this->contextEiType = $contextEiType;
		$this->treeLevel = $treeLevel;
	}
	
	/**
	 * @return int|null
	 */
	public function getTreeLevel() {
		return $this->treeLevel;
	}
	
	/**
	 * @return EiEntry[] 
	 */
	function getEiEntries() {
		return array_map(function ($arg) { return $arg->getEiEntry(); }, $this->typeDefs);
	}

	/**
	 * @param EiEntryGuiTypeDef[] $eiEntryGuiTypeDefs
	 */
	function putTypeDefs(array $eiEntryGuiTypeDefs) {
		foreach ($eiEntryGuiTypeDefs as $eiEntryGuiTypeDef) {
			ArgUtils::assertTrue($eiEntryGuiTypeDef instanceof EiEntryGui);
			$this->putTypeDef($eiEntryGuiTypeDef);
		}
	}
	
	/**
	 * @param EiEntryGuiTypeDef $eiEntryGuiTypeDef
	 */
	function putTypeDef(EiEntryGuiTypeDef $eiEntryGuiTypeDef) {
		$eiType = $eiEntryGuiTypeDef->getEiType();
		
		ArgUtils::assertTrue($eiType->isA($this->contextEiType));
		
		$this->typeDefs[$eiType->getId()] = $eiEntryGuiTypeDef;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiTypeDef[]
	 */
	function getTypeDefs() {
		return $this->typeDefs;
	}
	
	/**
	 * @param EiType $eiType
	 * @throws \OutOfBoundsException
	 * @return \rocket\ei\manage\gui\EiEntryGuiTypeDef
	 */
	function getTypeDefByEiType(EiType $eiType) {
		$eiTypeId = $eiType->getId();
		if (isset($this->typeDefs[$eiTypeId])) {
			return $this->typeDefs[$eiTypeId];
		}
		
		throw new \OutOfBoundsException('No EiEntryGuiTypeDef for passed EiType available: ' . $eiType);
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$eiTypeId = $siEntryInput->getTypeId();
		
		if (!isset($this->typeDefs[$eiTypeId])) {
			throw new CorruptedSiInputDataException('EiType not available: ' . $eiTypeId);
		}
		
		$this->selectedEiTypeId = $eiTypeId;
		$this->typeDefs[$eiTypeId]->handleSiEntryInput($siEntryInput);
	}
	
	function isTypeDefSelected(): bool {
		return $this->selectedEiTypeId !== null;
	}
	
	/**
	 * @param string $eiTypeId
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function selectedTypeDef(string $eiTypeId) {
		if (isset($this->typeDefs[$eiTypeId])) {
			$this->selectedEiTypeId = $eiTypeId;
		}
		
		throw new \InvalidArgumentException('Unknown EiType id: ' . $eiTypeId);
		return $this;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\gui\EiEntryGuiTypeDef
	 */
	function getSelectedTypeDef() {
		if (!isset($this->eiEntryGuis[$this->selectedEiTypeId])) {
			throw new IllegalStateException('No selection');
		}
		
		return $this->eiEntryGuis[$this->selectedEiTypeId];
	}
	
	/**
	 * @return \rocket\si\content\SiEntryIdentifier
	 */
	function createSiEntryIdentifier() {
		$typeCategory = $this->contextEiType->getSupremeEiType()->getId();
		$id = null;
		if ($this->isTypeDefSelected()) {
			$id = $this->getSelectedTypeDef()->getEiEntry()->getPid();
		}
		
		return new SiEntryIdentifier($typeCategory, $id);
	}
	

	public function __toString() {
		return 'EiEntryGui of ' . $this->eiEntry;
	}
}