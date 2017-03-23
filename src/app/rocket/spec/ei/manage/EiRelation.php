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

use n2n\util\ex\IllegalStateException;

class EiRelation {
	private $eiFrame;
	private $eiEntry;
	
	public function __construct(EiFrame $eiFrame, EiEntry $eiEntry = null) {
		$this->eiFrame = $eiFrame;
		$this->eiEntry = $eiEntry;
	}
	
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	public function hasEiEntry(): bool {
		return $this->eiEntry !== null;
	}
	
	public function getEiEntry() {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		throw new IllegalStateException();
	}
}
