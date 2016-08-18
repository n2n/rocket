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

use n2n\util\ex\IllegalStateException;

class GuiSection {
	const COMMON = null;
	const MAIN = 'main';
	const ASIDE = 'aside';
	
	private $type;
	private $title;
	private $guiFieldOrder;
	
	public function getType() {
		return $this->type;
	}

	public function setType(string $type = null) {
		$this->type = $type;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getGuiFieldOrder() {
		if ($this->guiFieldOrder === null) {
			test($this->title);
			throw new IllegalStateException('No GuiFieldOrder defined.');
		}
		
		return $this->guiFieldOrder;
	}

	public function setGuiFieldOrder(GuiFieldOrder $guiFieldOrder) {
		$this->guiFieldOrder = $guiFieldOrder;
	}
	
	public function copy(array $guiFieldOrder = null) {
		$copy = new GuiSection();
		$copy->setTitle($this->getTitle());
		$copy->setType($this->getType());
		
		if ($guiFieldOrder !== null) {
			$copy->setGuiFieldOrder($guiFieldOrder);
		} else {
			$copy->setGuiFieldOrder($this->getGuiFieldOrder());
		}
		
		return $copy;
	}
	
	public static function getTypes(): array {
		return array(self::COMMON, self::MAIN, self::ASIDE);
	}
}
