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
namespace rocket\spec\ei\manage;

use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\web\dispatch\map\PropertyPath;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\gui\DisplayDefinition;

class EntryGui {
	private $eiEntryGui;
	private $entryPropertyPath;
	
	public function __construct(EiEntryGui $eiEntryGui, PropertyPath $propertyPath = null) {		
		$this->eiEntryGui = $eiEntryGui;
		$this->entryPropertyPath = $propertyPath;
		
		if ($propertyPath === null && !((bool) $eiEntryGui->getViewMode() & DisplayDefinition::READ_VIEW_MODES)) {
			throw new \InvalidArgumentException('PropertyPath required.');
		}
	}
	
	public function getEiEntryGui(): EntryGuiModel {
		return $this->eiEntryGui;
	}
	
	public function hasPropertyPath(): bool {
		return $this->entryPropertyPath !== null;	
	}
	
	public function getPropertyPath(): PropertyPath {
		if ($this->entryPropertyPath !== null) {
			return $this->entryPropertyPath;
		}
		
		throw new IllegalStateException('No EntryPropertyPath given.');
	}
}
