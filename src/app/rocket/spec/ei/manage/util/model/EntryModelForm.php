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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use n2n\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\dispatch\annotation\AnnoDispObject;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\model\EntryGuiModel;

class EntryModelForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('dispatchable', new AnnoDispObject());
	}

	private $entryGuiModel;
	private $dispatchable;

	public function __construct(EntryGuiModel $entryGuiModel) {
		$this->entryGuiModel = $entryGuiModel;
		$this->dispatchable = $entryGuiModel->getEiSelectionGui()->getDispatchable();
	}

	public function getEntryGuiModel(): EntryGuiModel {
		return $this->entryGuiModel;
	}
	
// 	public function getEiMask() {
// 		return $this->eiMask;
// 	}

// 	public function getEiSelectionGui() {
// 		return $this->eiSelectionGui;
// 	}

	public function getEiMapping(): EiMapping {
		return $this->entryGuiModel->getEiMapping();
	}

	public function getDispatchable() {
		return $this->dispatchable;
	}

	public function setDispatchable(Dispatchable $dispatchable = null) {
		$this->dispatchable = $dispatchable;
	}

	private function _validation() {}

	public function save() {
		$this->entryGuiModel->getEiSelectionGui()->save();
	}
}
