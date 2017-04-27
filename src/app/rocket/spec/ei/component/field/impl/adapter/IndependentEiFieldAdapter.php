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

use rocket\spec\ei\component\impl\IndependentEiComponentAdapter;
use rocket\spec\ei\component\field\indepenent\IndependentEiField;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\l10n\Lstr;
use n2n\util\StringUtils;
use rocket\spec\ei\component\EiConfigurator;

abstract class IndependentEiFieldAdapter extends IndependentEiComponentAdapter implements IndependentEiField {
	protected $parentEiField;
	protected $labelLstr;
		
	public final function createEiConfigurator(): EiConfigurator {
		return $this->createEiFieldConfigurator();
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new AdaptableEiFieldConfigurator($this);
	}

	public function getParentEiField() {
		return $this->parentEiField;
	}
	
	public function setParentEiField(EiField $parentEiField = null) {
		return $this->parentEiField = $parentEiField;
	}
	
	public function getLabelLstr(): Lstr {
		if ($this->labelLstr === null) {
			$this->labelLstr = new Lstr(StringUtils::pretty($this->getId()));
		}
		
		return $this->labelLstr;
	}

	public function setLabelLstr(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}
	
	public function equals($obj) {
		return $obj instanceof EiField && parent::equals($obj);
	}
}
