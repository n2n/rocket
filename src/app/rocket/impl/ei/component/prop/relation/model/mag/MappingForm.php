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
namespace rocket\impl\ei\component\prop\relation\model\mag;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\util\entry\form\EiuEntryForm;
use n2n\web\dispatch\annotation\AnnoDispScalar;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\entry\EiEntry;

class MappingForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('eiuEntryForm', new AnnoDispObject());
		$ai->p('orderIndex', new AnnoDispScalar());
	}

	private $entryLabel;
	private $iconType;
	private $eiEntry;
	private $eiuEntryForm;
	private $orderIndex;
	
	public function __construct(string $entryLabel, string $iconType, EiEntry $eiEntry = null, 
			EiuEntryForm $eiuEntryForm = null, int $orderIndex = null) {
		ArgUtils::assertTrue($eiEntry !== null || $eiuEntryForm !== null);
		
		$this->entryLabel = $entryLabel;
		$this->iconType = $iconType;
		$this->eiEntry = $eiEntry;
		$this->eiuEntryForm = $eiuEntryForm;
		$this->orderIndex = $orderIndex;
	}
	
	public function isAccessible(): bool {
		return $this->eiuEntryForm !== null;
	}
	
	public function getEntryLabel(): string {
		return $this->entryLabel;
	}
	
	public function getIconType() {
		return $this->iconType;
	}
	
	public function buildEiEntry() {
		if ($this->eiuEntryForm !== null) {
			return $this->eiuEntryForm->buildEiuEntry()->getEiEntry();
		}
		
		return $this->eiEntry;
	}
	
	public function getEiuEntryForm() {
		return $this->eiuEntryForm;
	}
	
	public function setEiuEntryForm(EiuEntryForm $eiuEntryForm) {
		$this->eiuEntryForm = $eiuEntryForm;
	}

	public function getOrderIndex() {
		return $this->orderIndex;
	}

	public function setOrderIndex($orderIndex) {
		$this->orderIndex = $orderIndex;
	}
	
	private function _validation() {}
}
