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
namespace rocket\impl\ei\component\prop\relation\model\relation;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\ei\manage\frame\EiFrameListener;
use rocket\ei\manage\entry\EiEntry;

class MappedRelationEiModificator implements EiFrameListener {
	private $targetEiFrame;
	private $relationEntry;
	private $targetEiPropPath;
	private $sourceMany;

	public function __construct(EiFrame $targetEiFrame, RelationEntry $relationEntry, EiPropPath $targetEiPropPath, bool $sourceMany) {
		$this->targetEiFrame = $targetEiFrame;
		$this->relationEntry = $relationEntry;
		$this->targetEiPropPath = $targetEiPropPath;
		$this->sourceMany = (boolean) $sourceMany;
	}
	
	public function onNewEiEntry(EiEntry $eiEntry) {
// 		$eiFrame = $eiu->frame()->getEiFrame();
// 		$eiEntry = $eiu->entry()->getEiEntry();
		
		if (/*$this->targetEiFrame !== $eiFrame
				||*/ !$eiEntry->getEiObject()->isNew()) return;

		if (!$this->sourceMany) {
			$eiEntry->setValue($this->targetEiPropPath, $this->relationEntry);
			return;
		}
		
		$value = $eiEntry->getValue($this->targetEiPropPath);
		if ($value === null) {
			$value = new \ArrayObject();
		}
		$value[] = $this->relationEntry;
		$eiEntry->setValue($this->targetEiPropPath, $value);
	}
}
