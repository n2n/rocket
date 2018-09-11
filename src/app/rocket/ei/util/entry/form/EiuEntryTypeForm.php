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
namespace rocket\ei\util\entry\form;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\util\gui\EiuEntryGui;

class EiuEntryTypeForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('dispatchable', new AnnoDispObject());
	}

	private $eiuEntryGui;
	private $dispatchable;

	public function __construct(EiuEntryGui $eiuEntryGui) {
		$this->eiuEntryGui = $eiuEntryGui;
		$this->dispatchable = $eiuEntryGui->getEiEntryGui()->getDispatchable();
	}

	/**
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	public function getEiuEntryGui() {
		return $this->eiuEntryGui;
	}

	public function getDispatchable() {
		return $this->dispatchable;
	}

	public function setDispatchable(Dispatchable $dispatchable = null) {
		$this->dispatchable = $dispatchable;
	}

	private function _validation() {}

	public function save() {
		$this->eiuEntryGui->getEiEntryGui()->save();
	}
}
