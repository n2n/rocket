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

use rocket\spec\ei\manage\draft\Draft;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\control\EntryNavPoint;

class LiveEiSelection extends EiSelectionAdapter {
	private $liveEntry;

	public function __construct(LiveEntry $liveEntry) {
		$this->liveEntry = $liveEntry;
	}

	public function isNew(): bool {
		return !$this->liveEntry->isPersistent();
	}
	
	public function getLiveEntry(): LiveEntry {
		return $this->liveEntry;
	}

	public function isDraft(): bool {
		return false;
	}

	public function getDraft(): Draft {
		throw new IllegalStateException('EiSelection contains no Draft.');
	}
	
	public function toEntryNavPoint(): EntryNavPoint {
		return new EntryNavPoint($this->liveEntry->getId());
	}
	
	public static function create(EiSpec $eiSpec, $entityObj) {
		if ($entityObj === null) return null;
		return new LiveEiSelection(LiveEntry::createFrom($eiSpec, $entityObj));
	}
}
