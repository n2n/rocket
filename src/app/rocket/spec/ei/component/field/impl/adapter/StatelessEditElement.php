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
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\Editable;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\util\model\Eiu;

class StatelessEditElement extends StatelessDisplayElement implements Editable {
	private $statelessEditable;
	private $eiu;
	private $mag;
	
	public function __construct(StatelessEditable $statelessEditable, Eiu $eiu) {
		parent::__construct($statelessEditable, $eiu);
		
		$this->statelessEditable = $statelessEditable;
		$this->eiu = $eiu;
	}

	public function isMandatory(): bool {
		return $this->statelessEditable->isMandatory($this->eiu);
	}
	
	public function isReadOnly(): bool {
		return $this->statelessEditable->isReadOnly($this->eiu);
	}
	
	public function getEditable(): Editable {
		return $this;
	}
	
	public function createMag(): Mag {
		if ($this->mag !== null) {
			throw new IllegalStateException('Option already created.');
		}
		
		$mag = $this->statelessEditable->createMag( $this->eiu);
		ArgUtils::valTypeReturn($mag, Mag::class, $this->statelessEditable, 'createMag');
		$this->statelessEditable->loadMagValue($this->eiu, $mag);
		return $this->mag = $mag;
	}
	
	public function save() {
		if ($this->mag === null) {
			throw new IllegalStateException('No option created.');
		}

		$this->statelessEditable->saveMagValue($this->mag, $this->eiu);
	}
}
