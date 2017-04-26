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
namespace rocket\spec\config\mask\model;

use rocket\spec\ei\manage\EntryGui;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use rocket\spec\ei\manage\util\model\EiuEntryGui;

class EiuEntryGuiTree {
	private $eiuEntryGuiTreeItems = array();	
	
	public function addByLevel(int $level, EiuEntryGui $eiuEntryGui) {
		$this->eiuEntryGuiTreeItems[$eiuEntryGui->getEiuEntry()->getLiveIdRep()] 
				= new EiEntryGuiTreeItem($level, $eiuEntryGui);
	}
	
	public function getEntryGuiTreeItems(): array {
		return $this->eiuEntryGuiTreeItems;
	}
	
	public function getEiuEntryGuis(): array {
		$entryGuis = array();
		foreach ($this->eiuEntryGuiTreeItems as $idRep => $entryGuiTreeItem) {
			$entryGuis[$idRep] = $entryGuiTreeItem->getEntryGui();
		}
		return $entryGuis;
	}
	
	public function getLevelByIdRep(string $idRep): int {
		if (isset($this->eiuEntryGuiTreeItems[$idRep])) {
			return $this->eiuEntryGuiTreeItems[$idRep]->getLevel();
		}
		
		throw new UnknownEntryException();
	}
}

class EiuEntryGuiTreeItem {
	private $level;
	private $entryGui;
	
	public function __construct(int $level, EntryGui $entryGui) {
		$this->level = $level;
		$this->entryGui = $entryGui;
	}
	
	public function getLevel(): int {
		return $this->level;
	}
	
	public function getEntryGui(): EntryGui {
		return $this->entryGui;
	}
}
